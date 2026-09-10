<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\SalaryRate;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendanceController extends Controller
{
public function index()
{
    if (!session()->has('user_id')) {
        return redirect('/login');
    }

    [$periodStart, $periodEnd] = $this->currentCutoffRange();

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
        }

        foreach ($recs as $rec) {
            $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
            if ($dayNum >= 1 && $dayNum <= 15) {
                $obj->{"day_{$dayNum}"} = strtolower($rec->status) === 'present' ? 'P' : '-';
            }
        }

        return $obj;
    })->values();

    return view('attendance.attendance', compact('records'));
}

    // ─── Determine current cutoff period based on today's date ───
    private function currentCutoffRange($cutoffType = null)
    {
        $today = now();
        $isFirst = $cutoffType ? $cutoffType === '1st' : $today->day <= 15;

        $start = $isFirst
            ? $today->copy()->startOfMonth()
            : $today->copy()->startOfMonth()->addDays(15);

        $end = $isFirst
            ? $today->copy()->startOfMonth()->addDays(14)
            : $today->copy()->endOfMonth();

        return [$start, $end, $isFirst ? '1st' : '2nd'];
    }

    // ─── SALARY PAGE ───────────────────────────────
    public function salary()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $rates = SalaryRate::all()->keyBy('position');

        [$periodStart, $periodEnd] = $this->currentCutoffRange();

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

            for ($i = 1; $i <= 15; $i++) {
                $obj->{"day_{$i}"} = null;
            }

            foreach ($recs as $rec) {
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                if ($dayNum >= 1 && $dayNum <= 15) {
                    $obj->{"day_{$dayNum}"} = strtolower($rec->status) === 'present' ? 'P' : 'A';
                }
            }

            return $obj;
        })->values();

        // Latest generated payroll per employee (for showing computed amounts already generated)
        $salaryMap = Payroll::orderByDesc('generated_at')->get()->unique('name')->keyBy('name');

        return view('attendance.salary', compact('rates', 'attendance', 'salaryMap'));
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

    // ─── GENERATE PAYROLL ───────────────────────────
    public function storePayroll(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $cutoffType = $request->input('cutoff_type', '1st');
        $applyGovt = $request->input('apply_govt_deductions') === '1';
        $holidays = $request->input('holidays', []); // [day_number => 'regular'|'special']

        [$periodStart, $periodEnd] = $this->currentCutoffRange($cutoffType);

        $rates = SalaryRate::all()->keyBy('position');
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

            $basicPay = 0;
            $totalDays = 0;

            foreach ($recs as $rec) {
                if (strtolower($rec->status) !== 'present') continue;

                $totalDays++;
                $day = Carbon::parse($rec->attendance_date)->day;
                $holidayType = $holidays[$day] ?? null;
                $multiplier = $holidayType === 'regular' ? 2.0 : ($holidayType === 'special' ? 1.3 : 1.0);
                $basicPay += $dailyRate * $multiplier;
            }

            // NOTE: placeholder computation only — palitan mo ito ng tamang
            // SSS / PhilHealth / Pag-IBIG / withholding tax brackets.
            $sss = 0; $philhealth = 0; $pagibig = 0; $withholding = 0;
            if ($applyGovt) {
                $sss = round($basicPay * 0.045, 2);
                $philhealth = round($basicPay * 0.02, 2);
                $pagibig = 100.00;
                $withholding = round(max(0, $basicPay - 20833) * 0.15, 2);
            }

            $totalDeductions = $sss + $philhealth + $pagibig + $withholding;
            $netSalary = $basicPay - $totalDeductions;

            Payroll::create([
                'employee_id' => $first->employee_id,
                'name' => $name,
                'category' => $category,
                'payroll_period_start' => $periodStart->format('Y-m-d'),
                'payroll_period_end' => $periodEnd->format('Y-m-d'),
                'cutoff_type' => $cutoffType,
                'total_days' => $totalDays,
                'total_hours' => $recs->sum('total_hours'),
                'basic_salary' => $basicPay,
                'sss_deduction' => $sss,
                'philhealth_deduction' => $philhealth,
                'pagibig_deduction' => $pagibig,
                'withholding_tax' => $withholding,
                'deduction' => 0,
                'gross_pay' => $basicPay,
                'total_deductions' => $totalDeductions,
                'net_pay' => $netSalary,
                'net_salary' => $netSalary,
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

    // ─── MANUAL ENCODE ATTENDANCE ───────────────────
    public function storeAttendance(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'employee_id' => 'nullable|string',
            'employee_name' => 'required|string',
            'category' => 'required|string',
            'attendance_date' => 'required|date',
            'time_in' => 'nullable',
            'time_out' => 'nullable',
            'status' => 'required|string',
        ]);

        Attendance::updateOrCreate(
            [
                'employee_id' => $request->employee_id,
                'attendance_date' => $request->attendance_date,
            ],
            [
                'employee_name' => $request->employee_name,
                'category' => $request->category,
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
                'status' => $request->status,
            ]
        );

        return redirect()->route('attendance')->with('success', 'Attendance record saved.');
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
    [$periodStart, $periodEnd] = $this->currentCutoffRange();

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

        $imported = 0;
        foreach (array_chunk($allRows, 200) as $chunk) {
            Attendance::upsert(
                $chunk,
                ['employee_name', 'attendance_date'],   // unique key to match on
                ['category', 'status']                   // columns to update if match found
            );
            $imported += count($chunk);
        }

        return redirect()->route('attendance')->with('success', "Imported {$imported} attendance record(s) successfully!");

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
        } elseif (str_starts_with($val, '-')) {
            $status = 'absent';
        } else {
            continue;
        }

        $rows[] = [
            'employee_name' => $name,
            'attendance_date' => $periodStart->copy()->addDays($day - 1)->format('Y-m-d'),
            'category' => $category,
            'status' => $status,
        ];
    }

    return $rows;
}
}