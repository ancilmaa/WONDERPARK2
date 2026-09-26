<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        // Always fetch ALL (active and inactive) — the Active / Inactive
        // filter buttons in the view are client-side JS only (based on
        // each row's data-status attribute), so inactive records need to
        // be sent here too, not just the active ones.
        $employees = Employee::orderBy('employee_name')->get();

        $totalCount = $employees->count();
        $activeCount = $employees->where('status', 'active')->count();
        $inactiveCount = $employees->where('status', 'inactive')->count();

        return view('employees.index', compact(
            'employees', 'totalCount', 'activeCount', 'inactiveCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255|unique:employees,employee_name',
            'category' => 'required|in:Manager,Team Leader,Staff',
            'date_hired' => 'nullable|date',
            // Optional at creation — HR can set it now or later from the
            // Edit form. Exactly 4 digits when provided.
            'pin_code' => 'nullable|digits:4',
        ]);

        $validated['status'] = 'active';

        // Never store the PIN in plain text — hash it the same way a
        // password would be hashed. Left blank, pin_code simply stays
        // null until HR sets one later.
        if (!empty($validated['pin_code'])) {
            $validated['pin_code'] = Hash::make($validated['pin_code']);
        } else {
            unset($validated['pin_code']);
        }

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee added successfully.');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255|unique:employees,employee_name,' . $employee->id,
            'category' => 'required|in:Manager,Team Leader,Staff',
            'date_hired' => 'nullable|date',
            // Left blank, the employee's existing PIN is kept unchanged —
            // this form field is never pre-filled with the real PIN (it's
            // hashed and can't be reversed anyway), so a blank submit here
            // must NOT be treated as "clear the PIN".
            'pin_code' => 'nullable|digits:4',
        ]);

        if (!empty($validated['pin_code'])) {
            $validated['pin_code'] = Hash::make($validated['pin_code']);
        } else {
            // Don't overwrite the existing hash with null/empty.
            unset($validated['pin_code']);
        }

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function deactivate(Employee $employee)
    {
        $employee->update([
            'status' => 'inactive',
            'date_ended' => now(),
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee deactivated.');
    }

    public function activate(Employee $employee)
    {
        $employee->update([
            'status' => 'active',
            'date_ended' => null,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee reactivated.');
    }

    /**
     * Permanently deletes ALL employee records — used before uploading a
     * new CSV that already contains the full employee list, so there are
     * no duplicate/conflicting employee_name values (unique constraint)
     * during import.
     *
     * Force delete (not soft delete) because the old records need to be
     * truly gone from the unique index, so the new import isn't blocked
     * by matching names.
     *
     * Does NOT affect Attendance/Payroll history — that's a separate
     * table with no foreign key dependency here.
     */
    public function clearAll(Request $request)
    {
        $request->validate([
            'confirm_text' => 'required|string',
        ]);

        if (strtoupper(trim($request->confirm_text)) !== 'DELETE') {
            return redirect()->route('employees.index')
                ->with('error', 'Clear cancelled — you must type "DELETE" exactly.');
        }

        $count = Employee::withTrashed()->count();
        Employee::withTrashed()->forceDelete();

        if ($count === 0) {
            return redirect()->route('employees.index')
                ->with('success', 'No employee records to clear. You may proceed with importing your CSV.');
        }

        return redirect()->route('employees.index')
            ->with('success', "All {$count} employee record(s) have been cleared. You may now import your new CSV file.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('import_file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->with('error', 'Could not read the uploaded file.');
        }

        $header = fgetcsv($handle);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $nameIdx = array_search('employee_name', $header);
        $catIdx = array_search('category', $header);
        $dateIdx = array_search('date_hired', $header);

        if ($nameIdx === false || $catIdx === false) {
            fclose($handle);
            return back()->with('error', 'CSV must have "employee_name" and "category" columns.');
        }

        $validCategories = ['Manager', 'Team Leader', 'Staff'];
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            $name = trim($row[$nameIdx] ?? '');
            $category = trim($row[$catIdx] ?? '');
            $dateHired = $dateIdx !== false ? trim($row[$dateIdx] ?? '') : null;

            if ($name === '' || $category === '') {
                $errors[] = "Row {$rowNum}: missing name or category.";
                continue;
            }

            if (!in_array($category, $validCategories)) {
                $errors[] = "Row {$rowNum}: invalid category '{$category}'.";
                continue;
            }

            $exists = Employee::where('employee_name', $name)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            Employee::create([
                'employee_name' => $name,
                'category' => $category,
                'date_hired' => $dateHired ?: null,
                'status' => 'active',
            ]);

            $inserted++;
        }

        fclose($handle);

        $message = "Import complete. Added: {$inserted}, Skipped (already existed): {$skipped}.";
        if (count($errors) > 0) {
            $message .= ' Issues: ' . implode(' ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= ' (+' . (count($errors) - 5) . ' more)';
            }
        }

        return back()->with($inserted > 0 || $skipped > 0 ? 'success' : 'error', $message);
    }
}