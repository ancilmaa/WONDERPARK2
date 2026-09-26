<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryRate;
use App\Models\PayrollSetting;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // No more manual period switcher. The page always shows whichever
        // half-month period was most recently imported into (or last
        // touched by a manual/QR entry) — see latestCutoffRange() below.
        [$periodStart, $periodEnd, $cutoffType] = $this->latestCutoffRange();

        $attendance = Attendance::whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        // Pivot into day_1..day_15 shape, since that's what the view expects
        $records = $attendance->groupBy('employee_name')->map(function ($recs, $name) use ($periodStart) {
            $first = $recs->first();
            $obj = new \stdClass();
            $obj->name = $name;
            $obj->category = $first->category ?? 'Staff';

            for ($i = 1; $i <= 15; $i++) {
                $obj->{"day_{$i}"} = null;
                $obj->{"source_{$i}"} = null;
            }

            foreach ($recs as $rec) {
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                if ($dayNum >= 1 && $dayNum <= 15) {
                    // Prefer the exact bio-scan value ("+1.5", "-15", etc.) saved
                    // in `remarks`. Only fall back to a plain P/- when remarks is
                    // empty (e.g. records saved before this column existed).
                    $obj->{"day_{$dayNum}"} = $rec->remarks
                        ?: (strtolower($rec->status) === 'present' ? 'P' : '-');
                    // Carried alongside the value so the view can show a small
                    // dot indicating whether this came from the bio scanner,
                    // an employee's QR self check-in, or a manual HR entry.
                    $obj->{"source_{$dayNum}"} = $rec->source ?? 'bio';
                }
            }

            return $obj;
        })->values();

        // Every ACTIVE employee on file, grouped by category — powers the
        // clickable name chips in the Manual Entry modal so HR can tap a
        // name instead of typing it. Keyed by uppercase category to match
        // the <option value="Manager"> etc. selects in the view
        // (case-insensitive lookup happens on the JS side).
        //
        // Source is the Employees table (Phase 3) rather than derived from
        // past Attendance records, so a deactivated employee disappears
        // from these chips immediately.
        $employeesByCategory = Employee::active()
            ->orderBy('employee_name')
            ->get(['employee_name', 'category'])
            ->groupBy(fn($e) => strtoupper(trim($e->category ?? '')))
            ->map(fn($group) => $group->pluck('employee_name')->unique()->sort()->values());

        // Current QR token (if one has ever been generated) — needed here
        // so the QR modal opens with the CORRECT, currently-valid link from
        // the start. Without this, a fresh page load always shows the bare
        // /attendance/checkin URL with no token, which — once a token has
        // been generated at all — is itself treated as an expired/invalid
        // link by showCheckin(). The JS-side update after clicking
        // "Generate New QR" only lives in memory, so this is what keeps
        // the modal correct across a page reload too.
        $currentToken = DB::table('app_settings')->where('key', 'checkin_qr_token')->value('value');

        return view('attendance.attendance', compact('records', 'cutoffType', 'periodStart', 'periodEnd', 'employeesByCategory', 'currentToken'));
    }

    // ─── Which half-month period to show, based on the latest import ───
    // Looks at the most recent `attendance_date` on file (import, manual
    // entry, and QR check-in all write to this same column) and derives
    // the period from THAT date — not from today's real-world date. This
    // means the screen always reflects the latest data that actually
    // exists, even if nobody has looked at the app in weeks.
    //
    // NOTE: uses MAX(attendance_date) rather than ordering by an
    // updated_at/created_at column, since the `attendance` table doesn't
    // have timestamp columns.
    private function latestCutoffRange()
    {
        $latestDate = Attendance::max('attendance_date');

        if (!$latestDate) {
            // No attendance data at all yet — nothing to auto-detect, so
            // just fall back to whichever half of the month today is in.
            return $this->currentCutoffRange();
        }

        return $this->cutoffRangeForDate(Carbon::parse($latestDate));
    }

    // ─── Same as latestCutoffRange(), but can force a half ───
    // Takes the MONTH from the most recent attendance on file (matching
    // the Attendance page), and only the $cutoffType ('1st'/'2nd')
    // overrides which half. Used by the Salary page and Generate Salary so
    // Attendance, Salary, and Payroll always look at the same period.
    private function latestCutoffRangeFor($cutoffType = null)
    {
        $latestDate = Attendance::max('attendance_date');
        $base = $latestDate ? Carbon::parse($latestDate) : now();

        if ($cutoffType) {
            $base = $base->copy()->day($cutoffType === '1st' ? 1 : 16);
        }

        return $this->cutoffRangeForDate($base);
    }

    // ─── Compute the 1st/2nd-half period boundaries for a given date ───
    private function cutoffRangeForDate(Carbon $date)
    {
        $isFirst = $date->day <= 15;

        $start = $isFirst
            ? $date->copy()->startOfMonth()
            : $date->copy()->startOfMonth()->addDays(15);

        $end = $isFirst
            ? $date->copy()->startOfMonth()->addDays(14)
            : $date->copy()->endOfMonth();

        return [$start, $end, $isFirst ? '1st' : '2nd'];
    }

    // ─── Determine cutoff period based on TODAY's real date ───
    // Still used by import (see importAttendance()) — which cares about
    // "what period is it right now", not "what was last imported". An
    // explicit $cutoffType ('1st'/'2nd') can override which half, but the
    // month/year always comes from today.
    private function currentCutoffRange($cutoffType = null)
    {
        $today = now();

        if ($cutoffType) {
            $isFirst = $cutoffType === '1st';

            $start = $isFirst
                ? $today->copy()->startOfMonth()
                : $today->copy()->startOfMonth()->addDays(15);

            $end = $isFirst
                ? $today->copy()->startOfMonth()->addDays(14)
                : $today->copy()->endOfMonth();

            return [$start, $end, $isFirst ? '1st' : '2nd'];
        }

        return $this->cutoffRangeForDate($today);
    }

    // ─── Extract late minutes / OT hours from remarks ("-15", "+1.5") ──
    // Same format used in buildWideAttendanceRows() for import: "+N" = OT
    // hours, "-N" (numeric) = late/undertime minutes.
    private function parseRemarks(?string $remarks): array
    {
        $remarks = trim((string) $remarks);

        if ($remarks === '' || $remarks === 'P' || $remarks === '-') {
            return ['late_minutes' => 0, 'ot_hours' => 0.0];
        }

        if (str_starts_with($remarks, '+')) {
            // "+1.5" = overtime hours from the bio scanner
            return ['late_minutes' => 0, 'ot_hours' => (float) substr($remarks, 1)];
        }

        if (str_starts_with($remarks, '-') && is_numeric(substr($remarks, 1))) {
            // "-15" = late/undertime by this many minutes
            return ['late_minutes' => (int) abs((float) $remarks), 'ot_hours' => 0.0];
        }

        return ['late_minutes' => 0, 'ot_hours' => 0.0];
    }

    // ─── SALARY PAGE ───────────────────────────────
    public function salary()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $rates = SalaryRate::all()->keyBy('position');
        $settings = PayrollSetting::current();

        // Same period as the Attendance page (latest imported data), so
        // what's shown here always matches what's on the Attendance Sheet.
        [$periodStart, $periodEnd, $cutoffType] = $this->latestCutoffRangeFor();

        $records = Attendance::whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        // Pivot attendance rows into day_1..day_15 shape, since that's what the view expects
        $attendance = $records->groupBy('employee_name')->map(function ($recs, $name) use ($periodStart) {
            $first = $recs->first();
            $obj = new \stdClass();
            $obj->name = $name;
            $obj->category = $first->category ?? 'Staff';
            $obj->days_worked = 0;

            for ($i = 1; $i <= 15; $i++) {
                $obj->{"day_{$i}"} = null;
            }

            foreach ($recs as $rec) {
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                if ($dayNum >= 1 && $dayNum <= 15) {
                    $obj->{"day_{$dayNum}"} = $rec->remarks
                        ?: (strtolower($rec->status) === 'present' ? 'P' : 'A');

                    // Days Worked = count of days where status = present.
                    // Includes "-15", "-40", "+1.5" etc. (late/undertime still
                    // counts as having worked), and this is the same value
                    // used by storePayroll() — so the two always match.
                    if (strtolower($rec->status) === 'present') {
                        $obj->days_worked++;
                    }
                }
            }

            return $obj;
        })->values();

        // Payroll generated FOR THIS PERIOD, and generated AFTER the last
        // import of attendance for this period. If a new file is imported,
        // any earlier payroll is treated as stale (not deleted — it stays
        // in Payroll History), so nothing shows here until Generate Salary
        // is run again.
        $importedAt = DB::table('attendance_imports')
            ->whereDate('period_start', $periodStart->format('Y-m-d'))
            ->whereDate('period_end', $periodEnd->format('Y-m-d'))
            ->value('imported_at');

        $salaryQuery = Payroll::whereDate('payroll_period_start', $periodStart->format('Y-m-d'))
            ->whereDate('payroll_period_end', $periodEnd->format('Y-m-d'));

        if ($importedAt) {
            $salaryQuery->where('generated_at', '>=', $importedAt);
        }

        $salaryMap = $salaryQuery
            ->orderByDesc('generated_at')
            ->get()
            ->unique('name')
            ->keyBy('name');

        return view('attendance.salary', compact(
            'rates', 'attendance', 'salaryMap', 'periodStart', 'periodEnd', 'cutoffType', 'settings'
        ));
    }

    public function updateRates(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'rates' => 'required|array',
            'rates.*' => 'required|numeric|min:0',
        ]);

        foreach ($request->rates as $position => $dailyRate) {
            SalaryRate::updateOrCreate(
                ['position' => $position],
                ['daily_rate' => $dailyRate]
            );
        }

        return redirect()->route('salary')->with('success', 'Daily rates updated successfully.');
    }

    // ─── UPDATE OVERTIME / LATE / HOLIDAY-OT SETTINGS ───────────
    public function updatePayrollSettings(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'ot_multiplier' => 'required|numeric|min:1',
            'holiday_ot_multiplier' => 'required|numeric|min:1',
            'minutes_per_day' => 'required|integer|min:1',
            'late_rate_multiplier' => 'required|numeric|min:0',
        ]);

        $settings = PayrollSetting::current();
        $settings->update($request->only([
            'ot_multiplier', 'holiday_ot_multiplier', 'minutes_per_day', 'late_rate_multiplier',
        ]));

        return redirect()->route('salary')->with('success', 'Overtime & deduction settings updated successfully.');
    }

    // ─── GENERATE PAYROLL ───────────────────────────
    public function storePayroll(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $cutoffType = $request->input('cutoff_type', '1st');
        $applyGovt = $request->input('apply_govt_deductions') === '1';
        $holidays = $request->input('holidays', []); // [day_number => 'regular'|'special']

        // Month comes from the most recent attendance (matches Attendance
        // and Salary page), and the selected cutoff in the modal decides
        // the half.
        [$periodStart, $periodEnd] = $this->latestCutoffRangeFor($cutoffType);

        $rates = SalaryRate::all()->keyBy('position');
        $settings = PayrollSetting::current();
        $hoursPerDay = $settings->minutes_per_day > 0 ? $settings->minutes_per_day / 60 : 8;

        $categoryToPosition = [
            'manager' => 'manager',
            'team leader' => 'team_leader',
            'staff' => 'staff',
        ];

        $records = Attendance::whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        if ($records->isEmpty()) {
            return redirect()->route('salary')->with('error', 'No attendance records found for this cutoff period.');
        }

        $generatedAt = now();
        $count = 0;

        foreach ($records->groupBy('employee_name') as $name => $recs) {
            $first = $recs->first();
            $category = $first->category ?: 'Staff';
            $positionKey = $categoryToPosition[strtolower($category)] ?? 'staff';
            $dailyRate = floatval($rates->get($positionKey)?->daily_rate ?? 0);
            $hourlyRate = $hoursPerDay > 0 ? $dailyRate / $hoursPerDay : 0;

            $basicPay = 0;
            $holidayPay = 0;
            $overtimePay = 0;
            $holidayOtPay = 0;
            $lateDeduction = 0;
            $totalDays = 0;

            foreach ($recs as $rec) {
                if (strtolower($rec->status) !== 'present') continue;

                $totalDays++;

                // "Day 1–15" in the holiday modal is relative to the start
                // of the cutoff (so Day 1 = the 16th on the 2nd cutoff),
                // not the day of the month.
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                $holidayType = $holidays[$dayNum] ?? null;
                $isHoliday = $holidayType !== null;
                $multiplier = $holidayType === 'regular' ? 2.0 : ($holidayType === 'special' ? 1.3 : 1.0);

                // Straight pay first for this day (kept separate from the
                // holiday premium so the payslip can show "Daily Rate" and
                // "Holiday" separately, not lumped together).
                $basicPay += $dailyRate;

                if ($isHoliday) {
                    $holidayPay += $dailyRate * ($multiplier - 1);
                }

                ['late_minutes' => $lateMinutes, 'ot_hours' => $otHours] = $this->parseRemarks($rec->remarks);

                if ($lateMinutes > 0 && $settings->minutes_per_day > 0) {
                    $lateDeduction += ($dailyRate / $settings->minutes_per_day) * $lateMinutes * $settings->late_rate_multiplier;
                }

                if ($otHours > 0) {
                    if ($isHoliday) {
                        $holidayOtPay += $otHours * $hourlyRate * $settings->holiday_ot_multiplier;
                    } else {
                        $overtimePay += $otHours * $hourlyRate * $settings->ot_multiplier;
                    }
                }
            }

            $grossPay = $basicPay + $holidayPay + $overtimePay + $holidayOtPay;

            // NOTE: placeholder computation only — replace with the correct
            // SSS / PhilHealth / Pag-IBIG / withholding tax brackets.
            $sss = 0; $philhealth = 0; $pagibig = 0; $withholding = 0;
            if ($applyGovt) {
                $sss = round($grossPay * 0.045, 2);
                $philhealth = round($grossPay * 0.02, 2);
                $pagibig = 100.00;
                $withholding = round(max(0, $grossPay - 20833) * 0.15, 2);
            }

            $totalDeductions = $sss + $philhealth + $pagibig + $withholding + $lateDeduction;
            $netSalary = $grossPay - $totalDeductions;

            Payroll::create([
                'employee_id' => $first->employee_id,
                'name' => $name,
                'category' => $category,
                'payroll_period_start' => $periodStart->format('Y-m-d'),
                'payroll_period_end' => $periodEnd->format('Y-m-d'),
                'cutoff_type' => $cutoffType,
                'total_days' => $totalDays,
                'present_days' => $totalDays,
                'daily_rate' => $dailyRate,
                'total_hours' => $recs->sum('total_hours'),
                'basic_salary' => $basicPay,
                'holiday_pay' => round($holidayPay, 2),
                'overtime_pay' => round($overtimePay, 2),
                'holiday_ot_pay' => round($holidayOtPay, 2),
                'sss_deduction' => $sss,
                'philhealth_deduction' => $philhealth,
                'pagibig_deduction' => $pagibig,
                'withholding_tax' => $withholding,
                'deduction' => round($lateDeduction, 2), // late/undertime deduction
                'gross_pay' => round($grossPay, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_pay' => round($netSalary, 2),
                'net_salary' => round($netSalary, 2),
                'payroll_status' => 'Pending',
                'generated_at' => $generatedAt,
            ]);

            $count++;
        }

        return redirect()->route('salary')->with('success', "Payroll generated for {$count} employee(s).");
    }

    // ─── PAYROLL HISTORY ────────────────────────────
    public function payrollHistory(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $batches = Payroll::selectRaw('generated_at as batch_id, payroll_period_end as pay_date, cutoff_type, generated_at')
            ->whereNotNull('generated_at')
            ->distinct()
            ->orderByDesc('generated_at')
            ->get();

        $selectedBatch = $request->query('batch') ?? optional($batches->first())->batch_id;

        $isLatestBatch = $selectedBatch
            && $batches->isNotEmpty()
            && (string) $selectedBatch === (string) $batches->first()->batch_id;

        $salaries = $selectedBatch
            ? Payroll::where('generated_at', $selectedBatch)->get()
            : collect();

        $totalEmployees = $salaries->count();
        $totalBasic = $salaries->sum('basic_salary');
        $totalDeductions = $salaries->sum(function ($sal) {
            return $sal->deduction + $sal->sss_deduction + $sal->philhealth_deduction
                + $sal->pagibig_deduction + $sal->withholding_tax;
        });
        $totalNet = $salaries->sum('net_salary');

        return view('attendance.payroll-history', compact(
            'batches', 'selectedBatch', 'isLatestBatch', 'salaries',
            'totalEmployees', 'totalBasic', 'totalDeductions', 'totalNet'
        ));
    }

    public function deleteBatch($batch)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        Payroll::where('generated_at', $batch)->delete();

        return redirect()->route('payroll-history')->with('success', 'Payroll batch deleted successfully.');
    }

    public function payslip($id)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $sal = Payroll::findOrFail($id);

        return view('attendance.payslip', compact('sal'));
    }

    // ─── MANUAL ENCODE ATTENDANCE (HR, e.g. from a paper logbook) ──
    public function storeAttendance(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            // Must be an existing AND active record in the Employees
            // table — no more typing a random/typo'd name, and it can't be
            // encoded under a deactivated employee either.
            'employee_name' => [
                'required', 'string',
                Rule::exists('employees', 'employee_name')->where('status', 'active'),
            ],
            'category' => 'required|string',
            'attendance_date' => 'required|date',
            'time_in' => 'nullable',
            'time_out' => 'nullable',
            'status' => 'required|string|in:present,absent',
            // Optional exact value (e.g. "+1.5", "-15") — falls back to a
            // plain P/- based on status when left blank.
            'remarks' => 'nullable|string|max:10',
        ], [
            'employee_name.exists' => 'This name is not registered, or is no longer active, in the Employees list.',
        ]);

        $remarks = $validated['remarks'] ?: ($validated['status'] === 'present' ? 'P' : '-');

        // Same employee + date can be saved more than once in one day —
        // e.g. Time In encoded in the morning, Time Out encoded in the
        // afternoon once it's known. So: look up whatever's already there
        // first, and only overwrite time_in/time_out when this submission
        // actually filled them in. Leaving a field blank keeps the value
        // from the earlier save instead of wiping it out.
        $existing = Attendance::where('employee_name', $validated['employee_name'])
            ->where('attendance_date', $validated['attendance_date'])
            ->first();

        Attendance::updateOrCreate(
            [
                'employee_name' => $validated['employee_name'],
                'attendance_date' => $validated['attendance_date'],
            ],
            [
                'category' => $validated['category'],
                'time_in' => $validated['time_in'] ?: ($existing->time_in ?? null),
                'time_out' => $validated['time_out'] ?: ($existing->time_out ?? null),
                'status' => $validated['status'],
                'remarks' => $remarks,
                'source' => 'manual',
            ]
        );

        return redirect()->route('attendance')->with('success', 'Attendance record saved manually.');
    }

    // ─── QR SELF CHECK-IN (employee scans a QR code with their phone) ──

    /**
     * Public check-in page — no login required, since the employee reaches
     * this by scanning a QR code with their own phone. Employees are
     * listed straight from the Employees table (Phase 3), so a
     * deactivated employee simply disappears from the dropdown.
     *
     * Phase 5: the URL now carries a ?token= that must match the current
     * value stored in app_settings (key = 'checkin_qr_token'). This is
     * what lets HR "revoke" an old printed QR just by generating a new
     * one — the old link keeps working as a URL, but the token inside it
     * no longer matches, so this page shows an "expired" screen instead
     * of the check-in form.
     *
     * If no token has ever been generated yet (fresh install, nobody has
     * clicked "Generate New QR" yet), we don't enforce anything — this
     * keeps the feature backward compatible instead of breaking check-in
     * for everyone the moment this ships.
     *
     * GET /attendance/checkin
     */
    public function showCheckin(Request $request)
    {
        $currentToken = DB::table('app_settings')->where('key', 'checkin_qr_token')->value('value');

        if ($currentToken && $request->query('token') !== $currentToken) {
            return view('attendance.expired');
        }

        $employees = Employee::active()
            ->orderBy('employee_name')
            ->get(['employee_name', 'category']);

        return view('attendance.checkin', compact('employees', 'currentToken'));
    }

    /**
     * Handle the "Time In" / "Time Out" tap from the QR check-in page.
     *
     * Phase 4: this is now PIN-protected. Since this page is public (no
     * login, reachable by anyone with the QR link), the PIN is what
     * actually proves the person tapping the button is the employee they
     * selected from the dropdown — the name alone is not enough.
     *
     * Phase 5: also re-checks the token that was submitted along with the
     * form (see the hidden `token` field in attendance.checkin). This
     * closes the gap where someone already had the check-in page open in
     * their browser BEFORE it got revoked — even if their page was
     * loaded under the old QR, the submit itself is rejected once the
     * token no longer matches.
     *
     * POST /attendance/checkin
     */
    public function storeCheckin(Request $request)
    {
        $currentToken = DB::table('app_settings')->where('key', 'checkin_qr_token')->value('value');

        if ($currentToken && $request->input('token') !== $currentToken) {
            $message = 'This QR code has expired. Please scan the current QR code posted for check-in.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 410);
            }

            return back()->with('error', $message);
        }

        $validated = $request->validate([
            'employee_name' => [
                'required', 'string',
                Rule::exists('employees', 'employee_name')->where('status', 'active'),
            ],
            'category' => 'required|string',
            'type' => 'required|in:in,out',
            // Exactly 4 digits — kept as a string so a leading zero (e.g.
            // "0512") isn't silently dropped.
            'pin' => ['required', 'digits:4'],
        ], [
            'employee_name.exists' => 'You could not be recognized as an active employee. Please contact HR.',
            'pin.required' => 'Please enter your 4-digit PIN.',
            'pin.digits' => 'PIN must be exactly 4 digits.',
        ]);

        $employee = Employee::where('employee_name', $validated['employee_name'])
            ->where('status', 'active')
            ->first();

        if (!$employee || !$employee->pin_code) {
            // No PIN has been set for this employee yet — reject rather
            // than silently letting anyone check in as them. HR needs to
            // set a PIN first from the Employee Management page.
            $message = 'No PIN has been set for your account yet. Please ask HR to set one.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        if (!Hash::check($validated['pin'], $employee->pin_code)) {
            $message = 'Incorrect PIN.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $today = now()->format('Y-m-d');
        $now = now()->format('H:i:s');

        $existing = Attendance::where('employee_name', $validated['employee_name'])
            ->where('attendance_date', $today)
            ->first();

        $timeIn = $existing->time_in ?? null;
        $timeOut = $existing->time_out ?? null;

        if ($validated['type'] === 'in') {
            $timeIn = $now;
        } else {
            $timeOut = $now;
        }

        Attendance::updateOrCreate(
            [
                'employee_name' => $validated['employee_name'],
                'attendance_date' => $today,
            ],
            [
                'category' => $validated['category'],
                'time_in' => $timeIn,
                'time_out' => $timeOut,
                'status' => 'present',
                'remarks' => 'P',
                'source' => 'qr',
            ]
        );

        $label = $validated['type'] === 'in' ? 'Timed in' : 'Timed out';
        $message = "{$label} successfully at " . now()->format('g:i A') . ". Thank you, {$validated['employee_name']}!";

        if ($request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Issue a brand new QR token, instantly invalidating every previously
     * printed/shared QR link (they all point at the old token). Admin/HR
     * only — gate this behind auth + role middleware on the route.
     *
     * Returns the full new check-in URL so the front-end can regenerate
     * the QR image and update the "Copy Link" text without a page reload.
     *
     * POST /attendance/qr/regenerate
     */
    public function regenerateQr(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $token = Str::random(32);

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'checkin_qr_token'],
            ['value' => $token, 'updated_at' => now()]
        );

        $url = route('attendance.checkin', ['token' => $token]);

        return response()->json([
            'token' => $token,
            'url' => $url,
        ]);
    }

    // ─── BIO FILE IMPORT (CSV or Excel) ─────────────
    public function importAttendance(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        if (!$request->hasFile('import_file')) {
            return redirect()->route('attendance')->with('error', 'No file uploaded.');
        }

        $file = $request->file('import_file');
        $ext = strtolower($file->getClientOriginalExtension());

        // Which half of the month this file's day-1..day-15 columns map
        // onto — no manual toggle. Always inferred from TODAY's real
        // date, since a bio file is normally uploaded shortly after its
        // cutoff period ends (e.g. uploading on the 16th almost always
        // means "here's the 1st-half data").
        [$periodStart, $periodEnd, $cutoffType] = $this->currentCutoffRange();

        $allRows = [];

        try {
            if ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                if (!$handle) {
                    return redirect()->route('attendance')->with('error', 'Unable to read the uploaded file.');
                }

                $header = fgetcsv($handle);
                if (!$header) {
                    fclose($handle);
                    return redirect()->route('attendance')->with('error', 'The uploaded CSV file is empty or invalid.');
                }
                $header = array_map(fn($h) => strtolower(trim($h)), $header);

                while (($row = fgetcsv($handle)) !== false) {
                    // Guard against column count mismatch (avoids array_combine() crash)
                    if (count($row) !== count($header)) {
                        $row = array_pad(array_slice($row, 0, count($header)), count($header), '');
                    }
                    $rowData = array_combine($header, $row);
                    $allRows = array_merge($allRows, $this->buildWideAttendanceRows($rowData, $periodStart));
                }
                fclose($handle);
            } else {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, false);

                if (empty($rows)) {
                    return redirect()->route('attendance')->with('error', 'The uploaded file is empty or invalid.');
                }

                $header = array_map(fn($h) => strtolower(trim($h ?? '')), $rows[0]);

                for ($i = 1; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    if (count($row) !== count($header)) {
                        $row = array_pad(array_slice($row, 0, count($header)), count($header), '');
                    }
                    $rowData = array_combine($header, $row);
                    $allRows = array_merge($allRows, $this->buildWideAttendanceRows($rowData, $periodStart));
                }
            }

            if (empty($allRows)) {
                return redirect()->route('attendance')->with('error', 'No valid attendance rows found in the file.');
            }

            // Full replace, scoped to bio data only: clear out whatever was
            // previously imported from the bio scanner for this exact
            // period before inserting the new file. This is what makes an
            // employee who's no longer in the new CSV actually disappear
            // from the sheet, instead of a stale row lingering forever.
            //
            // Deliberately scoped to source='bio' only — Manual Entry and
            // QR self check-in records in this same period (e.g. days
            // covered during a bio outage) are left untouched, since a bio
            // re-upload has no way of knowing about those and shouldn't
            // wipe them out.
            Attendance::whereBetween('attendance_date', [
                $periodStart->format('Y-m-d'),
                $periodEnd->format('Y-m-d'),
            ])->where('source', 'bio')->delete();

            $imported = 0;
            foreach (array_chunk($allRows, 200) as $chunk) {
                Attendance::upsert(
                    $chunk,
                    ['employee_name', 'attendance_date'],           // unique key to match on
                    ['category', 'status', 'remarks', 'source']       // columns to update if match found
                );
                $imported += count($chunk);
            }

            // Record when this period was last imported. Used by the
            // Salary page to know if a generated payroll is stale.
            DB::table('attendance_imports')->updateOrInsert(
                [
                    'period_start' => $periodStart->format('Y-m-d'),
                    'period_end' => $periodEnd->format('Y-m-d'),
                ],
                ['imported_at' => now()]
            );

            // No cutoff param needed in the redirect — index() auto-detects
            // the period from the data we just upserted (via MAX(attendance_date)),
            // so this newly imported CSV is what shows up immediately.
            return redirect()->route('attendance')
                ->with('success', "Imported {$imported} attendance record(s) into the " . ($cutoffType === '1st' ? '1st half (Days 1-15)' : '2nd half (Days 16-31)') . " successfully!");

        } catch (\Throwable $e) {
            return redirect()->route('attendance')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function buildWideAttendanceRows(array $rowData, Carbon $periodStart): array
    {
        $name = $rowData['name'] ?? null;
        if (!$name) return [];

        $category = $rowData['category'] ?? 'Staff';
        $rows = [];

        for ($day = 1; $day <= 15; $day++) {
            $val = trim((string)($rowData[(string)$day] ?? ''));
            if ($val === '') continue;

            $status = null;
            if ($val === 'P' || str_starts_with($val, '+')) {
                $status = 'present';
            } elseif ($val === '-') {
                // A bare dash with no number = genuinely absent that day.
                $status = 'absent';
            } elseif (str_starts_with($val, '-') && is_numeric(substr($val, 1))) {
                // "-15", "-30", etc. = late / undertime by that many minutes
                // — the employee still showed up and worked, so this still
                // counts as present for Days Worked and payroll purposes.
                $status = 'present';
            } else {
                continue;
            }

            $rows[] = [
                'employee_name' => $name,
                'attendance_date' => $periodStart->copy()->addDays($day - 1)->format('Y-m-d'),
                'category' => $category,
                'status' => $status,
                'remarks' => $val,
                'source' => 'bio',
            ];
        }

        return $rows;
    }
}