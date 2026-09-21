<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Payroll;
use App\Models\Attendance;
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

        // Every employee name seen so far, grouped by category — powers the
        // clickable name chips in the Manual Entry modal so HR can tap a name
        // instead of typing it. Keyed by uppercase category to match the
        // <option value="Manager"> etc. selects in the view (case-insensitive
        // lookup happens on the JS side).
        //
        // Uses each employee's MOST RECENT attendance record to decide their
        // category — not every distinct category they've ever been recorded
        // under. Without this, an employee who was once mis-encoded under
        // the wrong category (e.g. a typo during an early manual entry)
        // would keep showing up under that stale category forever, even
        // after being correctly recorded elsewhere.
        $employeesByCategory = Attendance::orderByDesc('attendance_date')
            ->get(['employee_name', 'category'])
            ->unique('employee_name') // first = latest, since sorted desc above
            ->groupBy(fn($e) => strtoupper(trim($e->category ?? '')))
            ->map(fn($group) => $group->pluck('employee_name')->unique()->sort()->values());

        return view('attendance.attendance', compact('records', 'cutoffType', 'periodStart', 'periodEnd', 'employeesByCategory'));
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

    // ─── Same as latestCutoffRange(), pero puwedeng pilitin ang half ───
    // Kinukuha ang BUWAN mula sa pinakabagong attendance na nasa file
    // (para tugma sa Attendance page), at ang $cutoffType ('1st'/'2nd')
    // lang ang nag-o-override kung aling kalahati. Ginagamit ng Salary
    // page at ng Generate Salary para laging iisang period ang tinitingnan
    // ng Attendance, Salary, at Payroll.
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

    // ─── Kumuha ng late minutes / OT hours mula sa remarks ("-15", "+1.5") ──
    // Ito ang parehong format na ginagamit sa buildWideAttendanceRows() para
    // sa import: "+N" = OT hours, "-N" (numeric) = late/undertime minutes.
    private function parseRemarks(?string $remarks): array
    {
        $remarks = trim((string) $remarks);

        if ($remarks === '' || $remarks === 'P' || $remarks === '-') {
            return ['late_minutes' => 0, 'ot_hours' => 0.0];
        }

        if (str_starts_with($remarks, '+')) {
            // "+1.5" = overtime hours mula sa bio scanner
            return ['late_minutes' => 0, 'ot_hours' => (float) substr($remarks, 1)];
        }

        if (str_starts_with($remarks, '-') && is_numeric(substr($remarks, 1))) {
            // "-15" = late/undertime nang ganito karaming minuto
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

                    // Days Worked = bilang ng araw na status = present.
                    // Kasama na rito ang "-15", "-40", "+1.5" at iba pa (late/
                    // undertime pa rin ay pumasok), at ito rin mismo ang
                    // ginagamit ng storePayroll() — kaya laging tugma.
                    if (strtolower($rec->status) === 'present') {
                        $obj->days_worked++;
                    }
                }
            }

            return $obj;
        })->values();

        // Payroll na na-generate PARA SA PERIOD NA ITO, at PAGKATAPOS pa ng
        // huling import ng attendance para sa period na ito. Kapag nag-import
        // ulit ng bagong file, ang mga naunang payroll ay itinuturing nang
        // luma (hindi binubura — nananatili sila sa Payroll History), kaya
        // walang computation na lalabas hanggang mag-Generate Salary ulit.
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

        // Buwan ng pinakabagong attendance (tugma sa Attendance at Salary
        // page), at ang napiling cutoff sa modal ang nagdedesisyon ng half.
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

                // "Day 1–15" sa holiday modal ay relative sa simula ng cutoff
                // (kaya Day 1 = petsa 16 sa 2nd cutoff), hindi ang araw ng buwan.
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                $holidayType = $holidays[$dayNum] ?? null;
                $isHoliday = $holidayType !== null;
                $multiplier = $holidayType === 'regular' ? 2.0 : ($holidayType === 'special' ? 1.3 : 1.0);

                // Straight pay muna para sa araw na ito (hiwalay sa holiday
                // premium para makita sa payslip ang "Daily Rate" at
                // "Holiday" nang hiwalay, hindi lump-sum).
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

            // NOTE: placeholder computation only — palitan mo ito ng tamang
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
            'employee_name' => 'required|string',
            'category' => 'required|string',
            'attendance_date' => 'required|date',
            'time_in' => 'nullable',
            'time_out' => 'nullable',
            'status' => 'required|string|in:present,absent',
            // Optional exact value (e.g. "+1.5", "-15") — falls back to a
            // plain P/- based on status when left blank.
            'remarks' => 'nullable|string|max:10',
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
     * listed from names already on file (this app has no separate
     * Employee table yet) so they just pick their name from a dropdown.
     *
     * GET /attendance/checkin
     */
    public function showCheckin()
    {
        // Same "most recent category wins" fix as employeesByCategory in
        // index() — avoids listing an employee twice (or under a stale
        // category) if they were ever mis-encoded under the wrong one.
        $employees = Attendance::orderByDesc('attendance_date')
            ->get(['employee_name', 'category'])
            ->unique('employee_name')
            ->sortBy('employee_name')
            ->values();

        return view('attendance.checkin', compact('employees'));
    }

    /**
     * Handle the "Time In" / "Time Out" tap from the QR check-in page.
     *
     * POST /attendance/checkin
     */
    public function storeCheckin(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => 'required|string',
            'category' => 'required|string',
            'type' => 'required|in:in,out',
        ]);

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

            // Itala kung kailan huling na-import ang period na ito. Ginagamit ng
            // Salary page para malaman kung luma na ang na-generate na payroll.
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