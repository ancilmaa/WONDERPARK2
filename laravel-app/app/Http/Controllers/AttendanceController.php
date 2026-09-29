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
        // FIX: the sheet can now be pointed at a specific half-month with
        // ?period=YYYY-MM-DD (the "View" dropdown, and the redirect after an
        // import / manual entry). Without it, fall back to auto-detect
        // (the half that contains the most recent attendance_date).
        // Before this, importing into a period OTHER than the one holding
        // the newest record left the sheet on the old period, so the import
        // looked like it "did nothing".
        $requestedPeriod = $request->query('period');
        $range = null;
        if ($requestedPeriod && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedPeriod)) {
            try {
                $range = $this->cutoffRangeForDate(Carbon::parse($requestedPeriod));
            } catch (\Throwable $e) {
                $range = null;
            }
        }
        [$periodStart, $periodEnd, $cutoffType] = $range ?? $this->latestCutoffRange();

        // How many actual days are in THIS period — always 15 for the 1st
        // half, but 13/14/15/16 for the 2nd half depending on the month
        // (Feb vs. a 30-day month vs. a 31-day month). Everything below
        // uses this instead of a hardcoded 15, so Day 16 on a 31-day
        // month's 2nd cutoff is no longer silently dropped.
        $periodDays = $periodStart->diffInDays($periodEnd) + 1;

        $attendance = Attendance::whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        // Pivot into day_1..day_N shape, since that's what the view expects
        $pendingNames = [];
        $records = $attendance->groupBy('employee_name')->map(function ($recs, $name) use ($periodStart, $periodDays, &$pendingNames) {
            $first = $recs->first();
            $obj = new \stdClass();
            $obj->name = $name;
            // Normalized so "Manager", "MANAGER", "manager" (inconsistent
            // casing from CSV imports / manual entry / QR check-in) all
            // collapse into one group instead of showing up as separate
            // duplicate categories in the table and the filter dropdown.
            $obj->category = $first->category ? ucwords(strtolower(trim($first->category))) : 'Staff';

            for ($i = 1; $i <= $periodDays; $i++) {
                $obj->{"day_{$i}"} = null;
                $obj->{"source_{$i}"} = null;
            }

            foreach ($recs as $rec) {
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                if ($dayNum >= 1 && $dayNum <= $periodDays) {
                    // Prefer the exact bio-scan value ("+1.5", "-15", etc.) saved
                    // in `remarks`. Only fall back to a plain P/- when remarks is
                    // empty (e.g. records saved before this column existed).
                    // QR Time In with no Time Out yet -> shown as "IN" (not
                    // counted as a worked day until they Time Out).
                    $isPending = ($rec->source ?? null) === 'qr' && empty($rec->time_out);
                    if ($isPending) {
                        $pendingNames[] = $name;
                    }

                    $obj->{"day_{$dayNum}"} = $isPending
                        ? 'IN'
                        : ($rec->remarks
                            ?: (strtolower($rec->status) === 'present' ? 'P' : '-'));
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
        $qrDate = DB::table('app_settings')->where('key', 'checkin_qr_date')->value('value') ?? now()->format('Y-m-d');

        // Choices for the "Import into period" dropdown. Default (first,
        // selected) = the period currently on screen, i.e. where the
        // QR/Manual entries already live.
        $periodOptions = $this->periodOptionsAround($periodStart);

        // Employees who timed in via QR but haven't timed out yet (shown as "IN").
        $pendingNames = array_values(array_unique($pendingNames));

        return view('attendance.attendance', compact('records', 'cutoffType', 'periodStart', 'periodEnd', 'periodDays', 'employeesByCategory', 'currentToken', 'qrDate', 'periodOptions', 'pendingNames'));
    }

    // ─── Half-month periods for the "View" and "Import into" dropdowns ───
    // Includes: the period on screen, the one before and after it, today's
    // period, and every period that already has attendance data. Newest
    // first. The one on screen is marked selected.
    private function periodOptionsAround(Carbon $displayedStart): array
    {
        $starts = [];
        $add = function (Carbon $d) use (&$starts) {
            [$ps] = $this->cutoffRangeForDate($d);
            $starts[$ps->format('Y-m-d')] = true;
        };

        $displayed = $this->cutoffRangeForDate($displayedStart)[0];
        $add($displayed);

        if ($displayed->day === 1) {
            $add($displayed->copy()->subMonthNoOverflow()->day(16));
            $add($displayed->copy()->day(16));
        } else {
            $add($displayed->copy()->day(1));
            $add($displayed->copy()->addMonthNoOverflow()->day(1));
        }

        $add(now());

        Attendance::query()->select('attendance_date')->distinct()->pluck('attendance_date')
            ->each(fn($d) => $add(Carbon::parse($d)));

        krsort($starts);

        $options = [];
        foreach (array_keys($starts) as $key) {
            [$ps, $pe] = $this->cutoffRangeForDate(Carbon::parse($key));
            $options[] = [
                'value' => $ps->format('Y-m-d'),
                'label' => ($ps->day === 1 ? '1st half' : '2nd half')
                    . ' (' . $ps->format('M j') . '–' . $pe->format('j, Y') . ')',
                'selected' => $key === $displayed->format('Y-m-d'),
            ];
        }

        return $options;
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
        $latestDate = $this->completedOnly(Attendance::query())->max('attendance_date');
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

    // ─── QR check-ins that haven't been timed out yet ───
    // A QR self check-in is only "complete" once the employee taps Time Out.
    // Until then (Time In only) the Attendance sheet shows it as "IN", but
    // it must NOT be counted in Salary / Payroll. This filter drops those
    // incomplete rows for Salary, Generate Payroll and the Salary page's
    // period detection. Bio and Manual rows are never affected (they have
    // no Time In / Time Out flow).
    private function completedOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('source')
              ->orWhere('source', '!=', 'qr')
              ->orWhereNotNull('time_out');
        });
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

        // Total days in THIS half (see index() for why this can't be a
        // hardcoded 15 — a 31-day month's 2nd cutoff has 16 days).
        $periodDays = $periodStart->diffInDays($periodEnd) + 1;

        // The 2nd-cutoff day count for the CURRENT month, regardless of
        // which half happens to be showing right now — needed so the
        // holiday modal can build the right number of rows even when HR
        // switches the cutoff dropdown from 1st to 2nd inside the modal.
        $daysInMonth = $periodStart->copy()->startOfMonth()->daysInMonth;
        $secondCutoffDays = $daysInMonth - 15;

        $records = $this->completedOnly(Attendance::query())->whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        // Pivot attendance rows into day_1..day_N shape, since that's what the view expects
        $attendance = $records->groupBy('employee_name')->map(function ($recs, $name) use ($periodStart, $periodDays) {
            $first = $recs->first();
            $obj = new \stdClass();
            $obj->name = $name;
            $obj->category = $first->category ? ucwords(strtolower(trim($first->category))) : 'Staff';
            $obj->days_worked = 0;

            for ($i = 1; $i <= $periodDays; $i++) {
                $obj->{"day_{$i}"} = null;
            }

            foreach ($recs as $rec) {
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                if ($dayNum >= 1 && $dayNum <= $periodDays) {
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
            'rates', 'attendance', 'salaryMap', 'periodStart', 'periodEnd', 'cutoffType', 'settings', 'periodDays', 'secondCutoffDays'
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

        $records = $this->completedOnly(Attendance::query())->whereBetween('attendance_date', [
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

        $record = Attendance::updateOrCreate(
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

        // FIX: force-write `source` and `remarks` directly. If the Attendance
        // model's $fillable doesn't list them, updateOrCreate() silently drops
        // them and the column default ('bio') is stored instead — which made
        // manual entries look like bio rows, so the next bio import deleted
        // them (it wipes every source='bio' row in the period).
        $record->forceFill(['source' => 'manual', 'remarks' => $remarks])->save();

        $savedPeriodStart = $this->cutoffRangeForDate(Carbon::parse($validated['attendance_date']))[0];

        return redirect()->route('attendance', ['period' => $savedPeriodStart->format('Y-m-d')])
            ->with('success', 'Attendance record saved manually.');
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
        $qrDate = DB::table('app_settings')->where('key', 'checkin_qr_date')->value('value') ?? now()->format('Y-m-d');

        if ($currentToken && $request->query('token') !== $currentToken) {
            return view('attendance.expired');
        }

        $employees = Employee::active()
            ->orderBy('employee_name')
            ->get(['employee_name', 'category']);

        return view('attendance.checkin', compact('employees', 'currentToken', 'qrDate'));
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

        // Petsa na itinakda ng admin nang huling mag-generate ng QR (tingnan
        // ang regenerateQr()) — hindi galing sa client/request, kaya hindi
        // ito puwedeng i-tamper ng sinumang mag-s-scan. Kung wala pang
        // na-set kailanman (fresh install), fallback sa ngayong araw.
        $qrDate = DB::table('app_settings')->where('key', 'checkin_qr_date')->value('value');
        $today = $qrDate ?: now()->format('Y-m-d');
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

        $record = Attendance::updateOrCreate(
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

        // FIX: same as manual entry — make sure `source` is really saved as
        // 'qr' even if the model's $fillable doesn't include it.
        $record->forceFill(['source' => 'qr', 'remarks' => 'P'])->save();

        $label = $validated['type'] === 'in' ? 'Timed in' : 'Timed out';
        $message = "{$label} successfully at " . now()->format('g:i A') . ". Thank you, {$validated['employee_name']}!";

        if ($validated['type'] === 'in') {
            $message .= ' Your attendance will be counted once you Time Out.';
        }

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
     * Also accepts an optional `for_date` — the date that every scan
     * against this QR will be recorded under (see storeCheckin()). Admin
     * only sets this, never the employee doing the scanning; this is what
     * lets HR generate a QR "for Day 7" during a bio outage, print or
     * display it just for that day, and have every scan land on the
     * correct date without trusting anything the scanning device sends.
     * Defaults to today when not provided.
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

        $validated = $request->validate([
            'for_date' => 'nullable|date',
        ]);

        $token = Str::random(32);
        $forDate = $validated['for_date'] ?? now()->format('Y-m-d');

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'checkin_qr_token'],
            ['value' => $token, 'updated_at' => now()]
        );

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'checkin_qr_date'],
            ['value' => $forDate, 'updated_at' => now()]
        );

        $url = route('attendance.checkin', ['token' => $token]);

        return response()->json([
            'token' => $token,
            'url' => $url,
            'for_date' => $forDate,
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

        $request->validate([
            'period_start' => 'nullable|date',
        ]);

        // FIX: which half of the month this file's day-1..day-N columns
        // map onto. This used to be derived from TODAY's real date, which
        // broke as soon as the bio file was uploaded AFTER the cutoff
        // ended (e.g. uploading on Oct 1 for the Sep 16-30 period): the
        // import targeted Oct 1-15 instead, so the protected QR/Manual
        // entries in Sep 16-30 were never looked at, and the sheet jumped
        // to a different period, making those entries seem to vanish.
        //
        // Now: use the period picked in the Import dropdown (period_start).
        // If none was sent, default to the period currently shown on the
        // Attendance sheet (where the QR/Manual entries are), and only
        // fall back to today's period when there is no data at all.
        if ($request->filled('period_start')) {
            [$periodStart, $periodEnd, $cutoffType] = $this->cutoffRangeForDate(Carbon::parse($request->input('period_start')));
        } else {
            [$periodStart, $periodEnd, $cutoffType] = $this->latestCutoffRange();
        }
        $periodDays = $periodStart->diffInDays($periodEnd) + 1;

        // Active employee names, normalized (lowercase + trimmed) for
        // case/whitespace-insensitive matching against the bio export —
        // "juan dela cruz" in the CSV should still match "Juan Dela Cruz"
        // in the Employees table. Used to keep the bio file from creating
        // "ghost" attendance rows for names that aren't real, active
        // employees (typos in the bio device, resigned employees still in
        // its memory, new hires not yet encoded in Employees).
        //
        // FIX: this now maps to the EXACT, canonical name as stored in the
        // Employees table (e.g. "Juan Dela Cruz"), not just `true`. Before
        // this fix, buildWideAttendanceRows() saved whatever casing the
        // bio file happened to use verbatim (e.g. "JUAN DELA CRUZ" or
        // "juan dela cruz"). If a QR/Manual entry for the same person had
        // already been saved earlier in the period under a different
        // casing (QR/Manual always use the Employees-table casing), the
        // two ended up as two DIFFERENT `employee_name` strings. That's
        // harmless at the database-row level (the bio-overwrite guard
        // below is already case-insensitive and correctly protects the
        // QR/Manual row from being deleted or overwritten) — but the
        // Attendance/Salary pages group rows with `groupBy('employee_name')`,
        // which is a literal, case-sensitive string match. The result:
        // the person appeared as TWO separate rows on the sheet — one
        // built from the bio import, one built from the single QR/Manual
        // day — making it look like that one day's entry had vanished
        // when it had actually just landed in a second, easy-to-miss row.
        // Normalizing every bio row to the same canonical name merges
        // everything back into a single row as expected.
        $knownNames = Employee::active()
            ->pluck('employee_name')
            ->mapWithKeys(fn($n) => [strtolower(trim($n)) => $n]);

        $allRows = [];
        $unmatchedNames = []; // rows skipped because the name isn't a known active employee

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
                    $allRows = array_merge($allRows, $this->buildWideAttendanceRows($rowData, $periodStart, $periodDays, $knownNames, $unmatchedNames));
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
                    $allRows = array_merge($allRows, $this->buildWideAttendanceRows($rowData, $periodStart, $periodDays, $knownNames, $unmatchedNames));
                }
            }

            if (empty($allRows) && empty($unmatchedNames)) {
                return redirect()->route('attendance')->with('error', 'No valid attendance rows found in the file.');
            }

            if (empty($allRows) && !empty($unmatchedNames)) {
                // Every single row was unmatched — most likely the wrong
                // file, or the Employees list hasn't been set up yet.
                return redirect()->route('attendance')->with('error',
                    'Walang na-import — lahat ng pangalan sa file ay hindi nakita sa listahan ng Employees: '
                    . implode(', ', array_unique($unmatchedNames)) . '.');
            }

            // ── PROTECT existing QR / Manual entries for this period ──
            // A bio export marks a day as "-" (absent) whenever the
            // employee did NOT physically scan the bio device that day —
            // which is EXACTLY what happens when they checked in via QR or
            // were encoded manually instead (no bio access, outage, etc).
            // Without this guard, the upsert() below would match on the
            // same (employee_name, attendance_date) key and blindly
            // overwrite that already-correct QR/Manual "present" record
            // with the bio file's "-", wiping out the very thing QR/Manual
            // entry exists to preserve.
            //
            // Fix: any employee+date combo that already has a non-bio
            // record for this period is dropped from the incoming bio
            // rows entirely — QR and Manual entries always win over a
            // same-day bio import, never the other way around.
            //
            // NOTE: this key is (and always was) built case-insensitively
            // (strtolower(trim(...))), so this protection itself already
            // matched correctly across casing differences. The bug that
            // made a protected day appear to "disappear" was downstream,
            // in how the SAVED employee_name casing affected grouping on
            // the Attendance/Salary pages — see the $knownNames fix above.
            $protectedKeys = Attendance::whereBetween('attendance_date', [
                    $periodStart->format('Y-m-d'),
                    $periodEnd->format('Y-m-d'),
                ])
                ->where('source', '!=', 'bio')
                ->get(['employee_name', 'attendance_date'])
                ->map(fn($r) => strtolower(trim($r->employee_name)) . '|' . Carbon::parse($r->attendance_date)->format('Y-m-d'))
                ->flip();

            $protectedCount = 0;
            $allRows = array_values(array_filter($allRows, function ($row) use ($protectedKeys, &$protectedCount) {
                $key = strtolower(trim($row['employee_name'])) . '|' . $row['attendance_date'];
                if (isset($protectedKeys[$key])) {
                    $protectedCount++;
                    return false;
                }
                return true;
            }));

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

            $successMessage = "Imported {$imported} attendance record(s) into Days {$periodStart->day}-{$periodEnd->day} ({$periodStart->format('M j')}–{$periodEnd->format('M j, Y')}) successfully!";

            if ($protectedCount > 0) {
                $successMessage .= " {$protectedCount} na entry mula sa QR/Manual ang hindi na-overwrite (na-preserve dahil mas prioritized ito sa bio file para sa araw na iyon).";
            }

            if (!empty($unmatchedNames)) {
                $uniqueUnmatched = array_unique($unmatchedNames);
                // Appended (not a separate flash key) so it's guaranteed to
                // show even if the view doesn't have a dedicated "warning"
                // banner style set up yet.
                $successMessage .= ' Hindi na-import ang ' . count($uniqueUnmatched)
                    . ' pangalan na wala sa listahan ng Employees: ' . implode(', ', $uniqueUnmatched)
                    . '. Idagdag muna sila sa Employees, tapos i-import ulit ang file.';
            }

            // No cutoff param needed in the redirect — index() auto-detects
            // the period from the data we just upserted (via MAX(attendance_date)),
            // so this newly imported CSV is what shows up immediately.
            return redirect()->route('attendance', ['period' => $periodStart->format('Y-m-d')])->with('success', $successMessage);

        } catch (\Throwable $e) {
            return redirect()->route('attendance')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ─── CLEAR ALL ATTENDANCE (para sa testing / fresh start) ──────
    // Binubura ang LAHAT ng attendance records — lahat ng period, lahat ng
    // source (bio, manual, qr). Kasama ring binubura ang import history
    // (attendance_imports), dahil kung hindi, mananatiling naka-tala doon
    // ang mga lumang "imported_at" timestamps na wala nang katumbas na
    // data — ito ang ginagamit ng Salary page para malaman kung "stale" na
    // ba ang isang generated payroll.
    //
    // Payroll records ay SADYANG HINDI kasama dito — nasa Payroll History
    // pa rin ang mga naunang generated na payslip, kahit mabura ang
    // attendance na pinagbatayan nito.
    public function clearAll(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        Attendance::query()->delete();
        DB::table('attendance_imports')->delete();

        return redirect()->route('attendance')->with('success', 'All attendance records have been cleared.');
    }

    private function buildWideAttendanceRows(
        array $rowData,
        Carbon $periodStart,
        int $periodDays = 15,
        ?\Illuminate\Support\Collection $knownNames = null,
        array &$unmatchedNames = []
    ): array {
        $name = $rowData['name'] ?? null;
        if (!$name) return [];

        // FIX: use the canonical, Employees-table casing of this name for
        // everything we save, instead of whatever casing the bio file
        // happened to use. See the long comment above $knownNames in
        // importAttendance() for why this matters — without it, a day
        // encoded via QR/Manual (saved under the Employees-table casing)
        // and the same person's days from a bio import (saved under
        // whatever casing the bio device exports) end up as two different
        // `employee_name` values, which splits them into two separate rows
        // on the Attendance/Salary pages (both of which group by
        // `employee_name` as a literal string).
        $canonicalName = $name;

        // Skip rows for names that aren't a known, active employee —
        // prevents the bio file from creating a "ghost" attendance record
        // that Salary/Payroll can't match to a real employee_id/category.
        // $knownNames being null (not passed) skips this check entirely,
        // so existing callers/tests that don't pass it keep working.
        if ($knownNames !== null) {
            $key = strtolower(trim($name));

            if (!$knownNames->has($key)) {
                $unmatchedNames[] = $name;
                return [];
            }

            $canonicalName = $knownNames->get($key);
        }

        $category = $rowData['category'] ?? 'Staff';
        $rows = [];

        for ($day = 1; $day <= $periodDays; $day++) {
            // Suporta sa dalawang posibleng header format ng bio export:
            // "day1", "day2"... o plain "1", "2"... Sinusubukan muna ang
            // "day{n}", tapos "{n}" bilang fallback, kaya hindi masisira
            // kahit alin sa dalawa ang gamit ng totoong device, o kahit
            // magpalit pa sila ng format balang araw.
            $val = trim((string)($rowData["day{$day}"] ?? $rowData[(string)$day] ?? ''));
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
                'employee_name' => $canonicalName,
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