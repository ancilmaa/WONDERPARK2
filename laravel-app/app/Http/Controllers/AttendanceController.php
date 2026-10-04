<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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
    // Minimum minutes between Time In and Time Out. Prevents accidental /
    // test taps (e.g. In 4:52 PM, Out 4:52 PM) from being counted as a
    // full paid day. Change the number to whatever HR prefers.
    private const MIN_MINUTES_BEFORE_TIMEOUT = 30;

    // Overtime is counted in steps of this many minutes, rounded DOWN:
    //   under 30 min -> 0     30 min -> 0.5h     1h20m -> 1h
    //   1h45m -> 1.5h         2h35m -> 2.5h      3h -> 3h
    // So OT is always a whole or half hour (1, 1.5, 2, 2.5, ...).
    private const OT_STEP_MINUTES = 30;

    public function index(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // The sheet can be pointed at a specific half-month with
        // ?period=YYYY-MM-DD (the "View" dropdown, and the redirect after an
        // import). Without it, fall back to auto-detect
        // (the half that contains the most recent attendance_date).
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
        // half, but 13/14/15/16 for the 2nd half depending on the month.
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
            // Normalized so "Manager", "MANAGER", "manager" all collapse
            // into one group.
            $obj->category = $first->category ? ucwords(strtolower(trim($first->category))) : 'Staff';

            for ($i = 1; $i <= $periodDays; $i++) {
                $obj->{"day_{$i}"} = null;
                $obj->{"source_{$i}"} = null;
                // Actual Time In / Time Out shown in the cell tooltip.
                $obj->{"tin_{$i}"} = null;
                $obj->{"tout_{$i}"} = null;
            }

            foreach ($recs as $rec) {
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                if ($dayNum >= 1 && $dayNum <= $periodDays) {
                    // Prefer the exact value ("+1", "-15", etc.) saved in
                    // `remarks`. Only fall back to a plain P/- when remarks is
                    // empty. QR Time In with no Time Out yet -> shown as "IN"
                    // (not counted as a worked day until they Time Out).
                    $isPending = ($rec->source ?? null) === 'qr' && empty($rec->time_out);
                    if ($isPending) {
                        $pendingNames[] = $name;
                    }

                    $obj->{"day_{$dayNum}"} = $isPending
                        ? 'IN'
                        : ($rec->remarks
                            ?: (strtolower($rec->status) === 'present' ? 'P' : '-'));
                    // Source: bio scanner / QR self check-in (old rows may be 'manual').
                    $obj->{"source_{$dayNum}"} = $rec->source ?? 'bio';

                    // Actual recorded times, so HR can see exactly when the
                    // employee came in / went out (e.g. to verify a late).
                    $obj->{"tin_{$dayNum}"} = $rec->time_in ? Carbon::parse($rec->time_in)->format('g:i A') : null;
                    $obj->{"tout_{$dayNum}"} = $rec->time_out ? Carbon::parse($rec->time_out)->format('g:i A') : null;
                }
            }

            return $obj;
        })->values();

        // Current QR token (if one has ever been generated) — so the QR
        // modal opens with the CORRECT, currently-valid link from the start.
        $currentToken = DB::table('app_settings')->where('key', 'checkin_qr_token')->value('value');
        $qrDate = DB::table('app_settings')->where('key', 'checkin_qr_date')->value('value') ?? now()->format('Y-m-d');

        // Choices for the "View period" dropdown.
        $periodOptions = $this->periodOptionsAround($periodStart);

        // Employees who timed in via QR but haven't timed out yet (shown as "IN").
        $pendingNames = array_values(array_unique($pendingNames));

        // Shift setup (times, grace/break, default shift per employee).
        $shiftSettings = $this->shiftSettings();
        $shiftReady = $this->columnExists('employees', 'shift');
        $employeeShifts = $this->employeeShiftMap();
        $shiftEmployees = Employee::active()->orderBy('employee_name')->pluck('employee_name')->unique()->values()->all();

        return view('attendance.attendance', compact(
            'records', 'cutoffType', 'periodStart', 'periodEnd', 'periodDays',
            'currentToken', 'qrDate', 'periodOptions', 'pendingNames',
            'shiftSettings', 'employeeShifts', 'shiftEmployees', 'shiftReady'
        ));
    }

    // ─── Live check para sa Attendance sheet ───
    // Tinatawag ng page tuwing ilang segundo. Ibinabalik nito ang isang
    // "signature" ng lahat ng attendance sa period na nakikita. Kapag
    // nagbago ang signature (may nag-Time In / Time Out / na-import),
    // kusang ire-refresh ng page ang table nang hindi nire-reload ang buong page.
    //
    // GET /attendance/live?period=YYYY-MM-DD
    public function liveSignature(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $requestedPeriod = $request->query('period');
        $range = null;
        if ($requestedPeriod && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedPeriod)) {
            try {
                $range = $this->cutoffRangeForDate(Carbon::parse($requestedPeriod));
            } catch (\Throwable $e) {
                $range = null;
            }
        }
        [$periodStart, $periodEnd] = $range ?? $this->latestCutoffRange();

        $rows = Attendance::whereBetween('attendance_date', [
                $periodStart->format('Y-m-d'),
                $periodEnd->format('Y-m-d'),
            ])
            ->orderBy('employee_name')
            ->orderBy('attendance_date')
            ->get(['employee_name', 'attendance_date', 'time_in', 'time_out', 'remarks', 'status', 'source']);

        return response()->json([
            'sig' => md5($rows->toJson()),
            'count' => $rows->count(),
        ]);
    }

    // ─── Half-month periods for the "View" dropdown ───
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
    // Uses MAX(attendance_date) (import and QR check-in both write to this
    // column) rather than updated_at/created_at, since the `attendance`
    // table doesn't have timestamp columns.
    private function latestCutoffRange()
    {
        $latestDate = Attendance::max('attendance_date');

        if (!$latestDate) {
            return $this->currentCutoffRange();
        }

        return $this->cutoffRangeForDate(Carbon::parse($latestDate));
    }

    // ─── Same as latestCutoffRange(), but can force a half ───
    // Used by the Salary page and Generate Salary so Attendance, Salary,
    // and Payroll always look at the same period.
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
    // Until then the Attendance sheet shows it as "IN", but it must NOT be
    // counted in Salary / Payroll. Bio rows are never affected.
    private function completedOnly($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('source')
              ->orWhere('source', '!=', 'qr')
              ->orWhereNotNull('time_out');
        });
    }

    // ─── Extract late minutes / undertime minutes / OT hours from remarks ──
    //
    // Formats understood:
    //   "+1"      overtime hours
    //   "-15"     LATE arrival past the shift's grace period, by this many
    //             minutes — the employee still worked a full day otherwise.
    //   "-20/+1"  late AND overtime on the same day
    //   "Nh"      UNDERTIME — hours actually worked when the full required
    //             shift length wasn't completed (e.g. "7h", "3.18h").
    //
    // late_minutes and undertime_minutes are returned separately on
    // purpose — storePayroll() prices them differently (late carries the
    // late penalty multiplier; undertime is a plain pro-rated reduction).
    private function parseRemarks(?string $remarks, int $minutesPerDay = 480): array
    {
        $remarks = trim((string) $remarks);

        if ($remarks === '' || $remarks === 'P' || $remarks === '-') {
            return ['late_minutes' => 0, 'undertime_minutes' => 0, 'ot_hours' => 0.0];
        }

        if (preg_match('/^(\d+(?:\.\d+)?)h$/i', $remarks, $m)) {
            // Hours actually worked, short of a full day — this is
            // UNDERTIME, never counted as "late".
            $shortage = (int) round($minutesPerDay - ((float) $m[1]) * 60);
            return ['late_minutes' => 0, 'undertime_minutes' => max(0, $shortage), 'ot_hours' => 0.0];
        }

        $late = 0;
        $ot = 0.0;
        foreach (explode('/', $remarks) as $part) {
            $part = trim($part);
            if (str_starts_with($part, '+') && is_numeric(substr($part, 1))) {
                $ot += (float) substr($part, 1);
            } elseif (str_starts_with($part, '-') && is_numeric(substr($part, 1))) {
                $late += (int) abs((float) $part);
            }
        }

        return ['late_minutes' => $late, 'undertime_minutes' => 0, 'ot_hours' => $ot];
    }

    // ═══════════════ SHIFTS (Opening / Mid / Closing) ═══════════════
    // Remarks formats written by QR check-in:
    //   P        full day (8 hours after break), nothing to report
    //   +1       1 whole hour of overtime (minutes are not paid)
    //   7h       hours actually worked when the day was short (undertime)
    // (Late formats "-20" and "-20/+1" still come from the bio file and
    //  from computeShiftRemarks().)

    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $k = $table . '.' . $column;

        if (!isset($cache[$k])) {
            try {
                $cache[$k] = Schema::hasColumn($table, $column);
            } catch (\Throwable $e) {
                $cache[$k] = false;
            }
        }

        return $cache[$k];
    }

    private function shiftSettings(): array
    {
        $defaults = [
            'opening' => ['label' => 'Opening', 'start' => '07:00', 'end' => '16:00'],
            'mid'     => ['label' => 'Mid',     'start' => '09:00', 'end' => '18:00'],
            'closing' => ['label' => 'Closing', 'start' => '12:00', 'end' => '21:00'],
        ];

        $keys = ['shift_grace_minutes', 'shift_break_minutes'];
        foreach (array_keys($defaults) as $k) {
            $keys[] = "shift_{$k}_start";
            $keys[] = "shift_{$k}_end";
        }

        try {
            $saved = DB::table('app_settings')->whereIn('key', $keys)->pluck('value', 'key');
        } catch (\Throwable $e) {
            $saved = collect();
        }

        $isTime = fn($v) => is_string($v) && preg_match('/^\d{2}:\d{2}$/', $v) === 1;

        $shifts = [];
        foreach ($defaults as $k => $sh) {
            $start = $saved->get("shift_{$k}_start");
            $end = $saved->get("shift_{$k}_end");
            $shifts[$k] = [
                'label' => $sh['label'],
                'start' => $isTime($start) ? $start : $sh['start'],
                'end' => $isTime($end) ? $end : $sh['end'],
            ];
        }

        $grace = $saved->get('shift_grace_minutes');
        $break = $saved->get('shift_break_minutes');

        return [
            'grace' => is_numeric($grace) ? (int) $grace : 15,
            'break' => is_numeric($break) ? (int) $break : 60,
            'shifts' => $shifts,
        ];
    }

    private function employeeShiftMap(): array
    {
        if (!$this->columnExists('employees', 'shift')) {
            return [];
        }

        return DB::table('employees')->whereNotNull('shift')->pluck('shift', 'employee_name')->all();
    }

    private function employeeShiftKey(string $name): ?string
    {
        if (!$this->columnExists('employees', 'shift')) {
            return null;
        }

        return DB::table('employees')->where('employee_name', $name)->value('shift') ?: null;
    }

    private function requiredMinutes(): int
    {
        $m = (int) (PayrollSetting::current()->minutes_per_day ?? 0);

        return $m > 0 ? $m : 480;
    }

    private function toMinutes(?string $t): ?int
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})/', trim((string) $t), $m)) {
            return null;
        }

        return ((int) $m[1]) * 60 + (int) $m[2];
    }

    // Minutes of overtime -> hours, in 30-minute steps (rounded down).
    private function otFromMinutes(int $minutes): float
    {
        $step = self::OT_STEP_MINUTES;

        return floor(max(0, $minutes) / $step) * ($step / 60);
    }

    // 1.0 -> "1", 1.5 -> "1.5", 2.5 -> "2.5"
    private function formatOt(float $hours): string
    {
        return rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.');
    }

    private function formatDutyHours(int $minutes): string
    {
        $h = round(max(0, $minutes) / 60, 2);
        $txt = rtrim(rtrim(number_format($h, 2, '.', ''), '0'), '.');

        return ($txt === '' ? '0' : $txt) . 'h';
    }

    /**
     * Returns the remarks value ("P", "-20", "+1", "-20/+1", "7h", ...) or
     * null when it can't be worked out (missing time in/out or times).
     *
     * NOTE: a "Nh" result here always means UNDERTIME (worked fewer hours
     * than required), never "late" — see parseRemarks().
     */
    private function computeShiftRemarks(?string $timeIn, ?string $timeOut, string $shiftStart, string $shiftEnd, int $grace, int $break, int $requiredMinutes): ?string
    {
        $in = $this->toMinutes($timeIn);
        $out = $this->toMinutes($timeOut);
        $start = $this->toMinutes($shiftStart);
        $end = $this->toMinutes($shiftEnd);

        if ($in === null || $out === null || $start === null || $end === null) {
            return null;
        }

        if ($end <= $start) {
            $end += 1440; // shift runs past midnight
        }
        if ($out < $in) {
            $out += 1440; // timed out after midnight
        }

        $lateRaw = max(0, $in - $start);
        $late = $lateRaw > $grace ? $lateRaw : 0;     // within grace = on time
        $underRaw = max(0, $end - $out);
        $under = $underRaw > $grace ? $underRaw : 0;  // leaving within grace = no undertime
        $otHours = $this->otFromMinutes($out - $end); // 30-minute steps (1, 1.5, 2, ...)

        // Paid length of the (possibly adjusted) schedule: the break is
        // taken out of any shift long enough to include one.
        $schedSpan = $end - $start;
        $schedDuty = $schedSpan - ($schedSpan >= 300 ? $break : 0);

        // Normal day: full-length schedule, nobody left early.
        if ($under === 0 && $schedDuty >= $requiredMinutes) {
            $parts = [];
            if ($late > 0) {
                $parts[] = '-' . $late;
            }
            if ($otHours > 0) {
                $parts[] = '+' . $this->formatOt($otHours);
            }

            return $parts ? implode('/', $parts) : 'P';
        }

        // Short day (early time-out, ...): report the hours actually
        // worked (UNDERTIME, not late).
        $effIn = ($in <= $start + $grace) ? $start : $in;
        $span = max(0, $out - $effIn);
        $duty = $span - ($span >= 300 ? $break : 0);

        // Full day = required hours, or short by no more than the grace period.
        if ($duty >= $requiredMinutes - $grace) {
            $extra = $this->otFromMinutes($duty - $requiredMinutes);

            return $extra > 0 ? '+' . $this->formatOt($extra) : 'P';
        }

        return $this->formatDutyHours($duty);
    }

    private function shiftRemarksFor(string $name, ?string $shiftKey, ?string $adjStart, ?string $adjEnd, ?string $timeIn, ?string $timeOut): ?string
    {
        if (!$timeIn || !$timeOut) {
            return null;
        }

        $cfg = $this->shiftSettings();
        $key = $shiftKey ?: $this->employeeShiftKey($name);

        if (!$key || !isset($cfg['shifts'][$key])) {
            // No shift known: judge by the hours actually worked.
            return $this->hoursOnlyRemarks($timeIn, $timeOut, $cfg['break']);
        }

        $sh = $cfg['shifts'][$key];

        return $this->computeShiftRemarks(
            $timeIn,
            $timeOut,
            $adjStart ?: $sh['start'],
            $adjEnd ?: $sh['end'],
            $cfg['grace'],
            $cfg['break'],
            $this->requiredMinutes()
        );
    }

    // Remarks based purely on hours worked (break deducted):
    //   P    = 8 hours (full day)
    //   +N   = overtime beyond 8h, in 30-minute steps (+1, +1.5, +2, +2.5 ...)
    //   Short by 15 minutes or less (the grace period) still counts as "P".
    //   Nh   = short of 8 hours — the hours actually worked (undertime)
    //
    // Examples (1 hour break, 8 hours required):
    //   7:00 AM -> 4:00 PM  = 9h - 1h = 8h   -> "P"
    //   7:00 AM -> 5:00 PM  = 10h - 1h = 9h  -> "+1"
    //   7:00 AM -> 3:00 PM  = 8h - 1h = 7h   -> "7h"
    private function hoursOnlyRemarks(?string $timeIn, ?string $timeOut, int $break): ?string
    {
        $in = $this->toMinutes($timeIn);
        $out = $this->toMinutes($timeOut);

        if ($in === null || $out === null) {
            return null;
        }

        if ($out < $in) {
            $out += 1440; // timed out after midnight
        }

        $span = $out - $in;
        $duty = $span - ($span >= 300 ? $break : 0);   // deduct the break
        $required = $this->requiredMinutes();          // 480 = 8 hours
        $grace = $this->shiftSettings()['grace'];      // 15 minutes by default

        // Full day: 8 hours, or short by no more than the grace period.
        if ($duty >= $required - $grace) {
            $otHours = $this->otFromMinutes($duty - $required);

            return $otHours > 0 ? '+' . $this->formatOt($otHours) : 'P';
        }

        return $this->formatDutyHours($duty);
    }

    // ─── Late check at Time In (shown right away to the employee) ───
    // Returns null when the employee has no shift assigned, otherwise:
    //   raw   = actual minutes after shift start
    //   late  = minutes counted as late (only if beyond the grace period)
    //   grace = grace period in minutes
    //   start = shift start, formatted (e.g. "9:00 AM")
    private function lateAtTimeIn(string $name, string $timeIn, ?string $adjStart = null): ?array
    {
        $key = $this->employeeShiftKey($name);
        $cfg = $this->shiftSettings();

        if (!$key || !isset($cfg['shifts'][$key])) {
            return null;
        }

        $startStr = $adjStart ?: $cfg['shifts'][$key]['start'];
        $in = $this->toMinutes($timeIn);
        $start = $this->toMinutes($startStr);

        if ($in === null || $start === null) {
            return null;
        }

        $raw = max(0, $in - $start);

        return [
            'raw'   => $raw,
            'late'  => $raw > $cfg['grace'] ? $raw : 0,
            'grace' => $cfg['grace'],
            'start' => Carbon::createFromFormat('H:i', substr($startStr, 0, 5))->format('g:i A'),
        ];
    }

    // ─── Which date a QR scan is recorded under ───
    // Default = the real date today. The day the admin picked when
    // generating the QR (checkin_qr_date) is honored — earlier OR later
    // than today — but only if the QR was generated within the last
    // 2 hours. After that it goes back to today's real date, so a
    // forgotten QR can't make later check-ins land on the wrong day.
    private function resolveCheckinDate(): string
    {
        $today = now()->toDateString();
        $row = DB::table('app_settings')->where('key', 'checkin_qr_date')->first();

        if (!$row || !$row->value || $row->value === $today) {
            return $today;
        }

        $generatedAt = !empty($row->updated_at) ? Carbon::parse($row->updated_at) : null;

        return ($generatedAt && $generatedAt->diffInMinutes(now()) <= 120)
            ? $row->value
            : $today;
    }

    private function checkinFail(Request $request, string $message, int $status = 422)
    {
        return $request->wantsJson()
            ? response()->json(['message' => $message], $status)
            : back()->with('error', $message);
    }

    // ─── SAVE SHIFT TIMES, GRACE/BREAK AND EMPLOYEE DEFAULT SHIFTS ───
    public function saveShifts(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'shifts' => 'required|array',
            'shifts.*.start' => 'required|date_format:H:i',
            'shifts.*.end' => 'required|date_format:H:i',
            'grace_minutes' => 'required|integer|min:0|max:120',
            'break_minutes' => 'required|integer|min:0|max:240',
            'employees' => 'nullable|array',
            'employees.*.name' => 'required|string',
            'employees.*.shift' => 'nullable|in:opening,mid,closing',
        ]);

        $set = function (string $key, $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => (string) $value, 'updated_at' => now()]
            );
        };

        foreach (['opening', 'mid', 'closing'] as $k) {
            if (isset($validated['shifts'][$k])) {
                $set("shift_{$k}_start", $validated['shifts'][$k]['start']);
                $set("shift_{$k}_end", $validated['shifts'][$k]['end']);
            }
        }
        $set('shift_grace_minutes', $validated['grace_minutes']);
        $set('shift_break_minutes', $validated['break_minutes']);

        $note = '';
        if (!empty($validated['employees'])) {
            if ($this->columnExists('employees', 'shift')) {
                foreach ($validated['employees'] as $row) {
                    DB::table('employees')
                        ->where('employee_name', $row['name'])
                        ->update(['shift' => $row['shift'] ?: null]);
                }
            } else {
                $note = ' Employee default shifts were NOT saved — run "php artisan migrate" first.';
            }
        }

        return back()->with('success', 'Shift settings saved.' . $note);
    }

    // ─── SALARY PAGE ───────────────────────────────
    public function salary()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $rates = SalaryRate::all()->keyBy('position');
        $settings = PayrollSetting::current();

        // Same period as the Attendance page (latest imported data).
        [$periodStart, $periodEnd, $cutoffType] = $this->latestCutoffRangeFor();

        // Total days in THIS half (a 31-day month's 2nd cutoff has 16 days).
        $periodDays = $periodStart->diffInDays($periodEnd) + 1;

        // The 2nd-cutoff day count for the CURRENT month, regardless of
        // which half happens to be showing right now (for the holiday modal).
        $daysInMonth = $periodStart->copy()->startOfMonth()->daysInMonth;
        $secondCutoffDays = $daysInMonth - 15;

        $records = $this->completedOnly(Attendance::query())->whereBetween('attendance_date', [
            $periodStart->format('Y-m-d'),
            $periodEnd->format('Y-m-d'),
        ])->get();

        // Pivot attendance rows into day_1..day_N shape
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
                    // Same value used by storePayroll(), so the two always match.
                    if (strtolower($rec->status) === 'present') {
                        $obj->days_worked++;
                    }
                }
            }

            return $obj;
        })->values();

        // Payroll generated FOR THIS PERIOD, and generated AFTER the last
        // import of attendance for this period (older ones are stale but
        // stay in Payroll History).
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

        // Does the payrolls table have the undertime_deduction column yet?
        // If not (migration not run), fold undertime into the same
        // `deduction` column as before.
        $hasUndertimeColumn = $this->columnExists('payrolls', 'undertime_deduction');

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
            $undertimeDeduction = 0;
            $totalDays = 0;

            foreach ($recs as $rec) {
                if (strtolower($rec->status) !== 'present') continue;

                $totalDays++;

                // "Day 1–15" in the holiday modal is relative to the start
                // of the cutoff (so Day 1 = the 16th on the 2nd cutoff).
                $dayNum = Carbon::parse($rec->attendance_date)->day - $periodStart->day + 1;
                $holidayType = $holidays[$dayNum] ?? null;
                $isHoliday = $holidayType !== null;
                $multiplier = $holidayType === 'regular' ? 2.0 : ($holidayType === 'special' ? 1.3 : 1.0);

                // Straight pay first (kept separate from the holiday premium
                // so the payslip can show "Daily Rate" and "Holiday" apart).
                $basicPay += $dailyRate;

                if ($isHoliday) {
                    $holidayPay += $dailyRate * ($multiplier - 1);
                }

                [
                    'late_minutes' => $lateMinutes,
                    'undertime_minutes' => $undertimeMinutes,
                    'ot_hours' => $otHours,
                ] = $this->parseRemarks($rec->remarks, (int) $settings->minutes_per_day);

                // LATE: arrived past the shift's grace period. Carries the
                // late penalty multiplier HR configured in Payroll Settings.
                if ($lateMinutes > 0 && $settings->minutes_per_day > 0) {
                    $lateDeduction += ($dailyRate / $settings->minutes_per_day) * $lateMinutes * $settings->late_rate_multiplier;
                }

                // UNDERTIME: worked a short/incomplete day. Priced as a plain
                // pro-rated reduction — multiplier of 1, never the late
                // penalty.
                if ($undertimeMinutes > 0 && $settings->minutes_per_day > 0) {
                    $undertimeDeduction += ($dailyRate / $settings->minutes_per_day) * $undertimeMinutes;
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

            $totalDeductions = $sss + $philhealth + $pagibig + $withholding + $lateDeduction + $undertimeDeduction;
            $netSalary = $grossPay - $totalDeductions;

            $payrollData = [
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
                // deduction = LATE only when the column split is available;
                // otherwise it carries both, same as before the split.
                'deduction' => round($hasUndertimeColumn ? $lateDeduction : ($lateDeduction + $undertimeDeduction), 2),
                'gross_pay' => round($grossPay, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_pay' => round($netSalary, 2),
                'net_salary' => round($netSalary, 2),
                'payroll_status' => 'Pending',
                'generated_at' => $generatedAt,
            ];

            if ($hasUndertimeColumn) {
                $payrollData['undertime_deduction'] = round($undertimeDeduction, 2);
            }

            Payroll::create($payrollData);

            $count++;
        }

        $note = $hasUndertimeColumn
            ? ''
            : ' (Paalala: hindi pa naka-run ang "php artisan migrate" — pansamantalang sama-sama pa ang Late at Undertime sa isang column.)';

        return redirect()->route('salary')->with('success', "Payroll generated for {$count} employee(s)." . $note);
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
            return $sal->deduction + ($sal->undertime_deduction ?? 0) + $sal->sss_deduction + $sal->philhealth_deduction
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

    // ─── BULK DELETE PAYROLL BATCHES ────────────────
    // Tumatanggap ng listahan ng generated_at values (isang batch bawat isa)
    // at binubura lahat ng payroll rows sa mga batch na iyon.
    //
    // DELETE /payroll-history/bulk-delete
    public function bulkDeleteBatches(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'batches'   => 'required|array|min:1',
            'batches.*' => 'required|string',
        ]);

        $deleted = DB::transaction(function () use ($request) {
            return Payroll::whereIn('generated_at', $request->batches)->delete();
        });

        return redirect()->route('payroll-history')
            ->with('success', count($request->batches) . " payroll batch(es) deleted ({$deleted} payslip record(s)).");
    }

    public function payslip($id)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $sal = Payroll::findOrFail($id);

        // Buod ng oras para sa payslip: ilang oras ang OT, ilang minuto
        // ang late at undertime sa cutoff na ito (galing sa attendance
        // remarks, pareho ng ginamit sa pag-generate ng payroll).
        $minutesPerDay = (int) (PayrollSetting::current()->minutes_per_day ?: 480);
        $summary = ['ot_hours' => 0.0, 'late_minutes' => 0, 'undertime_minutes' => 0];

        $recs = $this->completedOnly(Attendance::query())
            ->where('employee_name', $sal->name)
            ->whereBetween('attendance_date', [
                Carbon::parse($sal->payroll_period_start)->format('Y-m-d'),
                Carbon::parse($sal->payroll_period_end)->format('Y-m-d'),
            ])
            ->get();

        foreach ($recs as $rec) {
            if (strtolower($rec->status) !== 'present') {
                continue;
            }

            $p = $this->parseRemarks($rec->remarks, $minutesPerDay);
            $summary['ot_hours'] += $p['ot_hours'];
            $summary['late_minutes'] += $p['late_minutes'];
            $summary['undertime_minutes'] += $p['undertime_minutes'];
        }

        return view('attendance.payslip', compact('sal', 'summary'));
    }

    // ─── QR SELF CHECK-IN (employee scans a QR code with their phone) ──

    /**
     * Public check-in page — no login required. Employees are listed
     * straight from the Employees table, so a deactivated employee simply
     * disappears from the dropdown.
     *
     * The URL carries a ?token= that must match the current value stored
     * in app_settings (key = 'checkin_qr_token'). Generating a new QR
     * "revokes" every old printed one. If no token has ever been
     * generated, nothing is enforced (backward compatible).
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
     * - PIN-protected (the PIN proves the person tapping is the employee
     *   selected from the dropdown).
     * - Re-checks the QR token on submit, so a page opened before the QR
     *   was revoked can't still submit.
     * - Uses SERVER time (now()), never the phone's clock.
     * - Blocks double Time In, double Time Out, and Time Out without Time In.
     * - Tells the employee right away at Time In if they're late.
     * - On Time Out, remarks are computed from the hours worked:
     *   P (8h), +N (overtime hours), or Nh (undertime).
     *
     * POST /attendance/checkin
     */
    public function storeCheckin(Request $request)
    {
        $currentToken = DB::table('app_settings')->where('key', 'checkin_qr_token')->value('value');

        if ($currentToken && $request->input('token') !== $currentToken) {
            return $this->checkinFail(
                $request,
                'This QR code has expired. Please scan the current QR code posted for check-in.',
                410
            );
        }

        $validated = $request->validate([
            'employee_name' => [
                'required', 'string',
                Rule::exists('employees', 'employee_name')->where('status', 'active'),
            ],
            'category' => 'required|string',
            'type' => 'required|in:in,out',
            // Exactly 4 digits — kept as a string so a leading zero isn't dropped.
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
            return $this->checkinFail($request, 'No PIN has been set for your account yet. Please ask HR to set one.');
        }

        if (!Hash::check($validated['pin'], $employee->pin_code)) {
            return $this->checkinFail($request, 'Incorrect PIN.');
        }

        // Real date today by default; the admin's catch-up date only applies
        // for 2 hours after the QR was generated (see resolveCheckinDate()).
        $today = $this->resolveCheckinDate();
        $now = now()->format('H:i:s');

        $existing = Attendance::where('employee_name', $validated['employee_name'])
            ->where('attendance_date', $today)
            ->first();

        $timeIn = $existing->time_in ?? null;
        $timeOut = $existing->time_out ?? null;

        // No double Time In / Time Out, and no Time Out without Time In.
        if ($validated['type'] === 'in') {
            if ($timeIn) {
                return $this->checkinFail(
                    $request,
                    'Naka-Time In ka na kanina ng ' . Carbon::parse($timeIn)->format('g:i A') . '.'
                );
            }
            $timeIn = $now;
        } else {
            if (!$timeIn) {
                return $this->checkinFail($request, 'Wala ka pang Time In ngayong araw. Mag-Time In muna.');
            }
            if ($timeOut) {
                return $this->checkinFail(
                    $request,
                    'Naka-Time Out ka na kanina ng ' . Carbon::parse($timeOut)->format('g:i A') . '.'
                );
            }

            $worked = ($this->toMinutes($now) ?? 0) - ($this->toMinutes($timeIn) ?? 0);
            if ($worked < 0) {
                $worked += 1440;
            }
            if ($worked < self::MIN_MINUTES_BEFORE_TIMEOUT) {
                return $this->checkinFail(
                    $request,
                    'Masyadong maaga ang Time Out. Kailangan ng at least ' . self::MIN_MINUTES_BEFORE_TIMEOUT
                    . ' minutes mula sa Time In mo (' . Carbon::parse($timeIn)->format('g:i A') . ').'
                );
            }

            $timeOut = $now;
        }

        // On Time Out, work out the remarks:
        //  - Employee WITH a shift: late is checked against the shift start
        //    (15-minute grace), plus overtime in 30-minute steps.
        //      e.g. 7:10 in (within grace) / 4:00 PM out -> "P"
        //           7:20 in / 4:00 PM out                -> "-20"
        //           7:00 in / 5:45 PM out                -> "+1.5"
        //  - Employee WITHOUT a shift: judged by hours worked only
        //    (break deducted, 15-minute grace):
        //           7:00 AM -> 4:00 PM = 8h  -> "P"
        //           7:00 AM -> 5:30 PM = 9.5h -> "+1.5"
        //           7:00 AM -> 3:00 PM = 7h  -> "7h"
        $qrRemarks = 'P';
        if ($validated['type'] === 'out') {
            $qrRemarks = $this->shiftRemarksFor(
                $validated['employee_name'],
                null,
                $existing->adjusted_start ?? null,
                $existing->adjusted_end ?? null,
                $timeIn,
                $timeOut
            ) ?? 'P';
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

        // Make sure `source` is really saved as 'qr' even if the
        // model's $fillable doesn't include it.
        $record->forceFill(['source' => 'qr', 'remarks' => $qrRemarks])->save();

        // ── Message shown to the employee ──
        $label = $validated['type'] === 'in' ? 'Timed in' : 'Timed out';
        $message = "{$label} successfully at " . now()->format('g:i A') . ". Thank you, {$validated['employee_name']}!";

        if ($validated['type'] === 'in') {
            $info = $this->lateAtTimeIn(
                $validated['employee_name'],
                $timeIn,
                $existing->adjusted_start ?? null
            );

            if ($info === null) {
                $message .= ' (Walang naka-assign na shift sa iyo, kaya hindi makukuwenta ang late. Pakisabihan si HR.)';
            } elseif ($info['late'] > 0) {
                $message .= " Late ka ng {$info['raw']} minutes (shift start: {$info['start']}).";
            } elseif ($info['raw'] > 0) {
                $message .= " {$info['raw']} min after start, pero nasa loob ka pa ng {$info['grace']}-minute grace period.";
            } else {
                $message .= ' On time ka!';
            }

            $message .= ' Mabibilang ang attendance mo kapag nag-Time Out ka.';
        } elseif ($qrRemarks === 'P') {
            $message .= ' Kumpleto ang 8 oras mo ngayong araw.';
        } elseif (str_starts_with($qrRemarks, '+')) {
            $message .= ' Overtime ngayong araw: ' . substr($qrRemarks, 1) . ' oras.';
        } elseif (preg_match('/^\d+(\.\d+)?h$/i', $qrRemarks)) {
            $message .= " Record ngayong araw: {$qrRemarks} na-trabaho (kulang sa 8 oras, makikita ito bilang Undertime).";
        } else {
            $message .= " Record ngayong araw: {$qrRemarks}.";
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Issue a brand new QR token, instantly invalidating every previously
     * printed/shared QR link. Admin/HR only.
     *
     * Also accepts an optional `for_date` — a "catch-up" date for scans
     * (e.g. during a bio outage). It is set by the admin only, never by
     * the scanning device, and it only stays in effect for 2 hours
     * (see resolveCheckinDate()). Defaults to today.
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

        // Which half of the month this file's day-1..day-N columns map
        // onto: the period currently on screen. If none was sent, default
        // to the period currently shown on the Attendance sheet, and only
        // fall back to today's period when there is no data at all.
        if ($request->filled('period_start')) {
            [$periodStart, $periodEnd, $cutoffType] = $this->cutoffRangeForDate(Carbon::parse($request->input('period_start')));
        } else {
            [$periodStart, $periodEnd, $cutoffType] = $this->latestCutoffRange();
        }
        $periodDays = $periodStart->diffInDays($periodEnd) + 1;

        // Active employee names, normalized (lowercase + trimmed), mapped
        // to the canonical name as stored in the Employees table. Keeps
        // the bio file from creating "ghost" rows for unknown names, and
        // makes every bio row use the SAME casing as QR entries so the
        // sheet groups them into a single row per person.
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

            // ── PROTECT existing QR (and old Manual) entries for this period ──
            // A bio export marks a day as "-" whenever the employee did NOT
            // scan the bio device — which is exactly what happens when they
            // checked in via QR. Any employee+date that already has a
            // non-bio record is dropped from the incoming bio rows, so QR
            // entries always win over a same-day bio import.
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
            // period before inserting the new file. QR records in this
            // period are left untouched.
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
                $successMessage .= " {$protectedCount} na entry mula sa QR ang hindi na-overwrite (na-preserve dahil mas prioritized ito sa bio file para sa araw na iyon).";
            }

            if (!empty($unmatchedNames)) {
                $uniqueUnmatched = array_unique($unmatchedNames);
                $successMessage .= ' Hindi na-import ang ' . count($uniqueUnmatched)
                    . ' pangalan na wala sa listahan ng Employees: ' . implode(', ', $uniqueUnmatched)
                    . '. Idagdag muna sila sa Employees, tapos i-import ulit ang file.';
            }

            return redirect()->route('attendance', ['period' => $periodStart->format('Y-m-d')])->with('success', $successMessage);

        } catch (\Throwable $e) {
            return redirect()->route('attendance')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // ─── CLEAR ALL ATTENDANCE (para sa testing / fresh start) ──────
    // Binubura ang LAHAT ng attendance records — lahat ng period, lahat ng
    // source — pati ang import history (attendance_imports).
    // Payroll records ay SADYANG HINDI kasama; nasa Payroll History pa rin
    // ang mga naunang generated na payslip.
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

        // Use the canonical, Employees-table casing of this name for
        // everything we save, so bio and QR rows group together.
        $canonicalName = $name;

        // Skip rows for names that aren't a known, active employee.
        // $knownNames being null skips this check entirely.
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
            // Supports both header formats of the bio export: "day1",
            // "day2"... or plain "1", "2"...
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
                // — still present for Days Worked and payroll purposes.
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