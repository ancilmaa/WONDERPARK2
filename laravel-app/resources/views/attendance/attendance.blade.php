@extends('layouts.sidebar')

@section('title', 'Attendance')

@section('styles')
    @include('partials.ui-theme')
    <style>
        .toolbar-actions input[type="file"] {
            font-size: 12px;
            color: var(--ink-soft);
            padding: 9px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            background: var(--bg);
            max-width: 210px;
        }

        .table-box { scrollbar-width: none; -ms-overflow-style: none; }
        .table-box::-webkit-scrollbar { display: none; }

        .source-legend {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 18px;
        }
        .source-legend span { display: inline-flex; align-items: center; gap: 6px; }
        .source-legend .dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }
        .source-legend .dot-bio { background: var(--muted); opacity: .5; }
        .source-legend .dot-qr { background: var(--info); }

        .payroll-header {
            text-align: center;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 2px solid var(--ink);
            position: relative;
        }
        .payroll-header::after {
            content: "";
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 1px;
            background: var(--pink);
        }
        .payroll-header h3 { font-family: var(--font-display); font-size: 19px; font-weight: 700; letter-spacing: .6px; color: var(--ink); }
        .payroll-header p { font-size: 12px; color: var(--muted); margin-top: 4px; letter-spacing: .2px; }

        /* ---- Attendance grid (centered cells, overrides the shared table look) ---- */
        .att-table { min-width: 980px; font-size: 12px; }
        .att-table th, .att-table td { padding: 11px 6px; text-align: center; }
        .att-table thead th { padding: 10px 6px; }
        .att-table td:nth-child(2) { text-align: left; color: var(--ink); font-weight: 600; }
        .att-table .category-row td { text-align: left; padding: 8px 14px; }
        .week-end { border-right: 1px solid var(--line-strong) !important; }

        .cell-present { color: var(--present); font-weight: 700; }
        .cell-deduction { color: var(--danger); font-weight: 600; font-size: 10.5px; }
        .cell-rest { color: var(--rest); font-weight: 400; }

        /* QR Time In with no Time Out yet: not counted until Time Out */
        .cell-pending { color: var(--warn); font-weight: 700; font-size: 10px; letter-spacing: .04em; }
        .cell-pending.src-qr::after,
        .cell-present.src-qr::after,
        .cell-deduction.src-qr::after,
        .cell-present.src-manual::after,
        .cell-deduction.src-manual::after {
            content: '';
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            margin-left: 3px;
            vertical-align: super;
        }
        .cell-pending.src-qr::after,
        .cell-present.src-qr::after,
        .cell-deduction.src-qr::after { background: var(--info); }
        /* Older records from the former Manual Entry keep their dot so they stay recognizable. */
        .cell-present.src-manual::after,
        .cell-deduction.src-manual::after { background: var(--warn); }

        /* Past day with a QR Time In but no Time Out: click to fix */
        .cell-missing {
            background: none;
            border: 1px dashed var(--danger);
            color: var(--danger);
            font-weight: 700;
            font-size: 9px;
            letter-spacing: .04em;
            padding: 2px 5px;
            border-radius: 6px;
            cursor: pointer;
        }
        .cell-missing:hover { background: var(--danger); color: #fff; }

        .days-worked {
            background: var(--pink-pale);
            color: var(--ink);
            font-weight: 700;
            font-family: var(--font-display);
            font-size: 13px;
        }

        .att-table .total-row td {
            background: transparent;
            color: var(--ink);
            font-weight: 700;
            font-size: 13.5px;
            border-top: 3px double var(--ink);
            border-bottom: none;
            padding-top: 16px;
        }
        .att-table .total-row td:last-child { font-family: var(--font-display); font-size: 16px; color: var(--pink-deep); }

        #uploadBtn.loading { opacity: .6; pointer-events: none; }

        /* Upload overlay card */
        .upload-card {
            background: #fff;
            border-radius: 18px;
            padding: 34px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-md);
            max-width: 300px;
            width: 90%;
            text-align: center;
            animation: cardPop .3s cubic-bezier(.34, 1.56, .64, 1);
        }
        .upload-spinner {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 4px solid var(--pink-light);
            border-top-color: var(--pink-deep);
            animation: spin .8s linear infinite;
        }
        .upload-card .upload-title { font-family: var(--font-display); font-weight: 700; font-size: 16px; color: var(--ink); }
        .upload-card .upload-sub { font-size: 12.5px; color: var(--ink-soft); }

        /* QR modal */
        #qrCodeBox { display: flex; justify-content: center; padding: 18px 0 6px; }
        .qr-link-row { display: flex; gap: 8px; margin-top: 14px; }
        .qr-link-row input {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 12px;
            color: var(--ink-soft);
            background: var(--bg);
        }

        @media (max-width: 900px) {
            .toolbar-actions { width: 100%; }
            .toolbar-actions input[type="file"] { max-width: none; width: 100%; }
            .toolbar-actions button { width: 100%; }
            .att-table { min-width: 760px; font-size: 11px; }
            .att-table th, .att-table td { padding: 8px 4px; }
        }
        @media (max-width: 520px) {
            .att-table { min-width: 640px; font-size: 10px; }
            .payroll-header h3 { font-size: 14px; }
            .payroll-header p { font-size: 11px; }
        }

        @media print {
            body { background: #fff; }
            .app-shell { display: block; }
            .sidebar, .sidebar-overlay, .mobile-topbar, .toolbar, .stats-row, .no-print { display: none !important; }
            .content-body { padding: 0; max-width: none; }
            .table-box { box-shadow: none; border-radius: 0; padding: 0; overflow: visible; }
            .att-table { min-width: unset; width: 100%; font-size: 10px; }
            .att-table th, .att-table td { border: 1px solid #999; padding: 4px; }
            .cell-present { color: var(--present) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .cell-deduction { color: var(--danger) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .cell-missing { border: none; color: var(--danger) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .category-row td { background: var(--pink-light) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .days-worked { background: var(--pink-pale) !important; color: var(--ink) !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            thead th { background: var(--ink) !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
@endsection

@section('content')

    @php
        // A day counts as "present" (worked) unless it's a bare dash, which means genuinely absent.
        // "-15", "-30", etc. are late/undertime by that many minutes, but the employee still showed up,
        // so those still count toward Days Worked.
        if (!function_exists('isDayPresent')) {
            function isDayPresent($v): bool
            {
                $v = (string) $v;
                if ($v === 'P' || str_starts_with($v, '+')) return true;
                if ($v === '-' || $v === '' || $v === 'IN') return false;
                // "-15", "-20/+1" (late / late + OT)
                if (preg_match('/^-\d/', $v)) return true;
                // "7h", "6.5h", "0h" (short day, hours actually worked)
                if (preg_match('/^\d+(\.\d+)?h$/i', $v)) return true;
                return false;
            }
        }

        if (!function_exists('isShortHours')) {
            function isShortHours($v): bool
            {
                return (bool) preg_match('/^\d+(\.\d+)?h$/i', (string) $v);
            }
        }

        $totalEmployees = isset($records) ? $records->count() : 0;
        $grouped = isset($records) ? $records->groupBy('category') : collect();
        $totalCategories = $grouped->count();

        $days = $periodDays ?? 15;

        // ── Split each category by area ──
        // Order: Manager → Team Leader → Staff → others.
        // Within a category: DINO QUEST → ROLL JOY → WONDER RIDES, then those without an area.
        // The Manager has no area.
        // Ang area ay galing sa Employees page (isang lugar lang ang pag-assign).
        $areaOrder = $areaOptions ?? ['DINO QUEST', 'ROLL JOY', 'WONDER RIDES'];
        $catOrder = ['manager', 'team leader', 'staff'];
        $anyArea = isset($records) && $records->contains(fn($r) => !empty($r->area));

        $sections = collect();
        if (isset($records)) {
            $byCat = $grouped->sortBy(function ($g, $c) use ($catOrder) {
                $p = array_search(strtolower($c), $catOrder);
                return $p === false ? 99 : $p;
            });

            foreach ($byCat as $cat => $emps) {
                $isManager = strtolower($cat) === 'manager';
                $byArea = $emps->groupBy(fn($e) => (string) ($e->area ?? ''))
                    ->sortBy(function ($g, $a) use ($areaOrder, $isManager) {
                        $a = (string) $a;
                        if ($a === '') return $isManager ? -1 : 100;
                        $p = array_search($a, $areaOrder);
                        return $p === false ? 50 : $p;
                    });

                foreach ($byArea as $a => $g) {
                    $a = (string) $a;
                    $sections->push([
                        'category' => $cat,
                        'area' => $a,
                        'employees' => $g,
                        'unassigned' => $a === '' && !$isManager && $anyArea,
                    ]);
                }
            }
        }

        $totalDaysAll = 0;
        if (isset($records)) {
            foreach ($records as $rec) {
                $d = 0;
                for ($i = 1; $i <= $days; $i++) {
                    $v = $rec->{'day_' . $i} ?? '';
                    if (isDayPresent($v)) {
                        $d++;
                    }
                }
                $totalDaysAll += $d;
            }
        }
    @endphp

    <div class="toolbar no-print">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Bio Attendance</div>
            <h2 style="font-family: Arial, Helvetica, sans-serif">Bio Attendance Sheet</h2>
        </div>

        <div class="toolbar-actions">
            <form id="uploadForm" action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data"
                style="display:contents;">
                @csrf
                {{-- The bio file is imported into the period currently on screen (always the current half-month). --}}
                <input type="hidden" name="period_start" value="{{ $periodStart->format('Y-m-d') }}">
                <input type="file" name="import_file" required>
                <button id="uploadBtn" type="submit" class="btn-ghost">Import Bio File</button>
            </form>
            <button type="button" class="btn-ghost" id="openQrBtn">
                <i class="fa-solid fa-qrcode"></i> Show QR Code
            </button>
            <button class="btn-primary" onclick="window.print()">Print</button>

            <form id="clearAllForm" action="{{ route('attendance.clear-all') }}" method="POST" style="display:contents;">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-danger" id="openClearAllBtn">Clear All</button>
            </form>
        </div>
    </div>

    <div class="stats-row no-print" style="--cols:2;">
        <div class="stat-card">
            <div class="label">Employees</div>
            <div class="value">{{ $totalEmployees }}</div>
            <div class="sub">Currently on this sheet</div>
        </div>
        <div class="stat-card">
            <div class="label">Total Days Worked</div>
            <div class="value">{{ $totalDaysAll }}</div>
            <div class="sub">Across all employees</div>
        </div>
    </div>

    <div class="table-box">

        @if (isset($latestUpload))
            <div class="payroll-header">
                <h3>REXS AMUSEMENT COM. INC.</h3>
                <p>({{ $latestUpload->branch ?? 'DINO PLAY, THE OUTLET AT LIPA' }})</p>
                <p>Period Covered: {{ $latestUpload->period ?? 'December 16-31, 2025' }} &nbsp;&middot;&nbsp; Salary Date:
                    {{ $latestUpload->salary_date ?? 'January 10, 2026' }}</p>
            </div>
        @endif

        <div class="filter-bar no-print">
            <span class="filter-label">Filter</span>
            <input type="text" id="employeeSearch" placeholder="Search employee name&hellip;">
            <select id="categoryFilter">
                <option value="">All Categories</option>
                @php
                    // Always list every category, even ones with no records yet.
                    $filterCats = collect(['Manager', 'Team Leader', 'Staff'])
                        ->merge($grouped->keys())
                        ->map(fn($c) => ucwords(strtolower($c)))
                        ->unique()
                        ->values();
                @endphp
                @foreach ($filterCats as $cat)
                    <option value="{{ strtolower($cat) }}">{{ $cat }}</option>
                @endforeach
            </select>
            <select id="areaFilter">
                <option value="">All Areas</option>
                @foreach ($areaOrder as $a)
                    <option value="{{ strtolower($a) }}">{{ $a }}</option>
                @endforeach
            </select>
            <button type="button" class="filter-toggle" data-type="late" id="filterLate">Late / Absent</button>
            <button type="button" class="filter-toggle" data-type="overtime" id="filterOvertime">Overtime</button>
            <button type="button" class="filter-clear" id="filterClear">Clear filters</button>
        </div>

        <div class="source-legend no-print">
            <span><span class="dot dot-bio"></span> Bio scanner</span>
            <span><span class="dot dot-qr"></span> QR self check-in</span>
            <span><strong style="color:var(--warn);">IN</strong> Timed in, waiting for Time Out</span>
            <span><strong style="color:var(--danger);">NO OUT</strong> Forgot to Time Out (click to fix)</span>
            <span>Hover a day to see the actual Time In / Time Out</span>
        </div>

        <table class="att-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th colspan="2" style="text-align:left;padding-left:14px;">Name of Employee</th>
                    @for ($i = 1; $i <= $days; $i++)
                        <th class="{{ $i % 5 == 0 && $i != $days ? 'week-end' : '' }}">{{ $i }}</th>
                    @endfor
                    <th>Days Worked</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $no = 1;
                    $totalDays = 0;
                @endphp

                @if (isset($records) && count($records) > 0)

                    @foreach ($sections as $section)
                        @php
                            $category = $section['category'];
                            $area = $section['area'];
                            $employees = $section['employees'];
                        @endphp
                        <tr class="category-row" data-cat-row="{{ strtolower($category) }}"
                            data-area-row="{{ strtolower($area) }}">
                            <td colspan="{{ $days + 4 }}">
                                {{ strtoupper($category) }}@if ($area) &middot; {{ strtoupper($area) }}@elseif ($section['unassigned']) &middot; NO AREA YET @endif
                            </td>
                        </tr>

                        @foreach ($employees as $item)
                            @php
                                $daysWorked = 0;
                                $hasOvertime = false;
                                $hasLate = false;
                                for ($i = 1; $i <= $days; $i++) {
                                    $v = $item->{'day_' . $i} ?? '';
                                    if (isDayPresent($v)) {
                                        $daysWorked++;
                                    }
                                    if (str_starts_with((string) $v, '+')) {
                                        $hasOvertime = true;
                                    }
                                    if ((str_starts_with((string) $v, '-') && $v !== '') || isShortHours($v)) {
                                        // Covers both a bare "-" (absent) and "-15" (late);
                                        // both are worth flagging under the Late/Absent filter.
                                        $hasLate = true;
                                    }
                                }
                            @endphp
                            <tr data-name="{{ strtolower($item->name) }}" data-category="{{ strtolower($category) }}"
                                data-area="{{ strtolower($area) }}"
                                data-late="{{ $hasLate ? 1 : 0 }}" data-overtime="{{ $hasOvertime ? 1 : 0 }}">
                                <td>{{ $no++ }}</td>
                                <td colspan="2">{{ $item->name }}</td>

                                @for ($i = 1; $i <= $days; $i++)
                                    @php
                                        $val = $item->{'day_' . $i} ?? '';
                                        $src = $item->{'source_' . $i} ?? null;
                                        $weekEndClass = $i % 5 == 0 && $i != $days ? 'week-end' : '';
                                        $srcClass = $src === 'qr' ? 'src-qr' : ($src === 'manual' ? 'src-manual' : '');
                                        $srcTitle = $val === 'IN' ? 'Timed in, waiting for Time Out' : ($src === 'qr' ? 'QR self check-in' : ($src === 'manual' ? 'Manual entry' : 'Bio scanner'));

                                        // Actual recorded Time In / Time Out (QR entries have these; bio rows usually don't).
                                        $tin = $item->{'tin_' . $i} ?? null;
                                        $tout = $item->{'tout_' . $i} ?? null;
                                        $timeTxt = $tin ? ' | In: ' . $tin . ' · Out: ' . ($tout ?? '—') : '';
                                    @endphp

                                    <td class="{{ $weekEndClass }}" @if($val) title="{{ $srcTitle . $timeTxt }}" @endif>
                                        @if ($val === 'IN')
                                            @php
                                                // A "IN" on a day that already passed = forgot to Time Out.
                                                $cellDate = $periodStart->copy()->addDays($i - 1);
                                                $isPastDay = $cellDate->lt(now()->startOfDay());
                                            @endphp
                                            @if ($isPastDay)
                                                <button type="button" class="cell-missing"
                                                    title="No Time Out recorded. Click to fix."
                                                    data-name="{{ $item->name }}"
                                                    data-date="{{ $cellDate->format('Y-m-d') }}"
                                                    data-label="{{ $cellDate->format('M j') }}"
                                                    data-tin="{{ $tin }}">NO OUT</button>
                                            @else
                                                <span class="cell-pending src-qr">IN</span>
                                            @endif
                                        @elseif ($val === 'P' || str_starts_with((string) $val, '+'))
                                            <span class="cell-present {{ $srcClass }}">{{ $val === 'P' ? 'P' : $val }}</span>
                                        @elseif(str_starts_with((string) $val, '-') || isShortHours($val))
                                            <span class="cell-deduction {{ $srcClass }}">{{ $val }}</span>
                                        @else
                                            <span class="cell-rest">&ndash;</span>
                                        @endif
                                    </td>
                                @endfor

                                @php $totalDays += $daysWorked; @endphp
                                <td class="days-worked">{{ $daysWorked }}</td>
                            </tr>
                        @endforeach
                    @endforeach

                    <tr id="noMatchRow" class="no-print" style="display:none;">
                        <td colspan="{{ $days + 4 }}">No employees match your search or filters.</td>
                    </tr>

                    <tr class="total-row">
                        <td colspan="{{ $days + 3 }}" style="text-align:center;">
                            TOTAL SALARY FOR {{ strtoupper($latestUpload->branch ?? 'DINO PLAY, THE OUTLET AT LIPA') }}
                        </td>
                        <td>{{ $totalDays }}.00</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="{{ $days + 4 }}" style="padding:24px;color:var(--muted);">No attendance data found. Please import
                            a BIO file.</td>
                    </tr>
                @endif

            </tbody>
        </table>

        <div class="pagination no-print" id="pagination"></div>

    </div>

    <div id="uploadOverlay" class="modal-overlay no-print" aria-live="assertive">
        <div class="upload-card">
            <div class="upload-spinner"></div>
            <div class="upload-title">Uploading BIO file</div>
            <div class="upload-sub">Please wait, this won't take long&hellip;</div>
        </div>
    </div>

    <!-- QR CHECK-IN MODAL -->
    <div id="qrOverlay" class="modal-overlay no-print" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="qrTitle" style="max-width:380px;">
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Employee Self Check-In</div>
                    <h3 id="qrTitle">Attendance QR Code</h3>
                </div>
                <button type="button" class="modal-close" id="closeQrModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" style="text-align:center;">
                @php
                    // AUTO: the QR is always for today. Day number is relative to the start of the current cutoff.
                    $todayDayNum = $periodStart->copy()->startOfDay()->diffInDays(now()->startOfDay()) + 1;
                @endphp
                <div class="form-group full" style="text-align:left;margin-bottom:14px;">
                    <label>This QR is for</label>
                    <div style="padding:11px 14px;border:1px solid var(--line-strong);border-radius:10px;background:var(--bg);font-weight:700;color:var(--ink);">
                        Day {{ $todayDayNum }} ({{ now()->format('M j') }}) &middot; Today
                    </div>
                    <p class="form-hint">Auto-set to today. Scans are always recorded under today's real date.</p>
                </div>
                <div id="qrCodeBox"></div>
                <div class="qr-link-row">
                    <input type="text" id="qrLinkInput" readonly value="{{ request()->getSchemeAndHttpHost() . '/attendance/checkin' . ($currentToken ? '?token=' . $currentToken : '') }}">
                    <button type="button" class="btn-ghost" id="copyQrLinkBtn"><i class="fa-solid fa-copy"></i></button>
                </div>
                <button type="button" class="btn-ghost" id="regenQrBtn" style="width:100%;margin-top:10px;">
                    <i class="fa-solid fa-rotate"></i> Generate New QR
                </button>
                <p class="form-hint" style="margin-top:8px;">
                    This creates a new QR code and automatically invalidates the old one, even if it has already been printed.
                </p>
            </div>
        </div>
    </div>

    <!-- FIX MISSING TIME OUT MODAL -->
    <div id="fixOutOverlay" class="modal-overlay no-print" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="fixOutTitle" style="max-width:380px;">
            <form action="{{ route('attendance.fix-timeout') }}" method="POST">
                @csrf
                <input type="hidden" name="employee_name" id="fixOutName">
                <input type="hidden" name="attendance_date" id="fixOutDate">
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">Missing Time Out</div>
                        <h3 id="fixOutTitle">Add Time Out</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeFixOut" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="form-hint" id="fixOutInfo" style="margin-bottom:12px;"></p>
                    <div class="form-group full" style="margin-bottom:12px;">
                        <label for="fixOutTime">Time Out</label>
                        <input type="time" id="fixOutTime" name="time_out" required>
                    </div>
                    <div class="form-group full">
                        <label for="fixOutReason">Reason</label>
                        <input type="text" id="fixOutReason" name="reason" maxlength="200" required
                            placeholder="e.g. Forgot to Time Out">
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelFixOut">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Time Out
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CLEAR ALL ATTENDANCE: custom confirm modal (not the native browser confirm()) -->
    <div id="clearAllOverlay" class="modal-overlay no-print" aria-hidden="true">
        <div class="modal-card confirm-card" role="alertdialog" aria-modal="true" aria-labelledby="clearAllTitle">
            <div class="confirm-icon warn"><i class="fa-solid fa-trash"></i></div>
            <div class="confirm-body">
                <div class="confirm-title" id="clearAllTitle">Delete all attendance?</div>
                <div class="confirm-message">
                    This will delete <strong>all</strong> attendance records: every period, every source
                    (bio and QR). The import history will be cleared as well. <strong>This cannot be undone.</strong>
                </div>
            </div>
            <div class="confirm-foot">
                <button type="button" class="btn-ghost" id="cancelClearAll">Cancel</button>
                <button type="button" class="btn-danger" id="confirmClearAllBtn">
                    <i class="fa-solid fa-trash"></i> Yes, delete everything
                </button>
            </div>
        </div>
    </div>

    @include('partials.ui-toast')

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        const form = document.getElementById("uploadForm");
        const btn = document.getElementById("uploadBtn");
        const uploadOverlay = document.getElementById("uploadOverlay");

        form.addEventListener("submit", function() {
            btn.classList.add("loading");
            document.body.style.overflow = "hidden";
            uploadOverlay.classList.add("active");
        });

        window.addEventListener("pageshow", function() {
            uploadOverlay.classList.remove("active");
            document.body.style.overflow = "";
            btn.classList.remove("loading");
        });

        // ── QR Code modal ──
        const qrOverlay = document.getElementById('qrOverlay');
        const openQrBtn = document.getElementById('openQrBtn');
        const closeQrModalBtn = document.getElementById('closeQrModal');
        const qrCodeBox = document.getElementById('qrCodeBox');
        const qrLinkInput = document.getElementById('qrLinkInput');
        const copyQrLinkBtn = document.getElementById('copyQrLinkBtn');
        const regenQrBtn = document.getElementById('regenQrBtn');
        let qrInstance = null;
        let qrArmed = false;
        let qrArmTimeout = null;

        function openQrModal() {
            qrOverlay.classList.add('active');
            qrOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            if (!qrInstance && window.QRCode) {
                qrInstance = new QRCode(qrCodeBox, {
                    text: qrLinkInput.value,
                    width: 220,
                    height: 220,
                    colorDark: '#0F172A',
                    colorLight: '#ffffff',
                });
            }
        }

        function closeQrModal() {
            qrOverlay.classList.remove('active');
            qrOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        openQrBtn?.addEventListener('click', openQrModal);
        closeQrModalBtn?.addEventListener('click', closeQrModal);
        qrOverlay?.addEventListener('click', (e) => {
            if (e.target === qrOverlay) closeQrModal();
        });

        copyQrLinkBtn?.addEventListener('click', () => {
            qrLinkInput.select();
            navigator.clipboard?.writeText(qrLinkInput.value);
            showToast('Link copied!', 'success', { duration: 1800 });
        });

        // "Generate New QR": two-tap confirm before it fires, since this
        // instantly invalidates any old printed QR the moment it succeeds.
        regenQrBtn?.addEventListener('click', () => {
            if (!qrArmed) {
                qrArmed = true;
                regenQrBtn.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Click again to confirm';
                clearTimeout(qrArmTimeout);
                qrArmTimeout = setTimeout(() => {
                    qrArmed = false;
                    regenQrBtn.innerHTML = '<i class="fa-solid fa-rotate"></i> Generate New QR';
                }, 4000);
                return;
            }

            clearTimeout(qrArmTimeout);
            qrArmed = false;
            regenQrBtn.disabled = true;
            regenQrBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating&hellip;';

            fetch("{{ route('attendance.qr.regenerate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="_token"]')?.value,
                },
                // AUTO: the server always uses today's date, nothing to send.
                body: JSON.stringify({}),
            })
            .then(async (res) => {
                // Read as text first, so a server crash (500 page, 419 CSRF page, etc.)
                // still shows something useful in the toast.
                const raw = await res.text();
                let data = {};
                try { data = JSON.parse(raw); } catch (e) { /* not JSON */ }

                if (!res.ok) {
                    const detail = data.message
                        || (raw ? raw.replace(/<[^>]*>/g, ' ').trim().slice(0, 160) : '');
                    throw new Error(`(HTTP ${res.status}) ${detail || 'Failed to generate a new QR code.'}`);
                }

                qrLinkInput.value = data.url;

                if (qrInstance && qrInstance.makeCode) {
                    qrInstance.makeCode(data.url);
                } else if (window.QRCode) {
                    qrCodeBox.innerHTML = '';
                    qrInstance = new QRCode(qrCodeBox, {
                        text: data.url,
                        width: 220,
                        height: 220,
                        colorDark: '#0F172A',
                        colorLight: '#ffffff',
                    });
                }

                showToast(`New QR code generated for ${data.for_date}. Any previously printed QR is now expired.`, 'success', { duration: 3200 });
            })
            .catch((err) => {
                showToast(err.message || 'Something went wrong. Please try again.', 'error', { duration: 6000 });
            })
            .finally(() => {
                regenQrBtn.disabled = false;
                regenQrBtn.innerHTML = '<i class="fa-solid fa-rotate"></i> Generate New QR';
            });
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (qrOverlay.classList.contains('active')) closeQrModal();
                if (clearAllOverlay.classList.contains('active')) closeClearAllModal();
            }
        });

        // ── Fix missing Time Out ──
        // Event delegation, because the live update replaces the whole table
        // (listeners attached directly to the buttons would be lost).
        const fixOutOverlay = document.getElementById('fixOutOverlay');

        function closeFixOut() {
            fixOutOverlay.classList.remove('active');
            fixOutOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', (e) => {
            const b = e.target.closest('.cell-missing');
            if (!b) return;

            document.getElementById('fixOutName').value = b.dataset.name;
            document.getElementById('fixOutDate').value = b.dataset.date;
            document.getElementById('fixOutTime').value = '';
            document.getElementById('fixOutReason').value = '';
            document.getElementById('fixOutInfo').textContent =
                `${b.dataset.name} · ${b.dataset.label} · Time In: ${b.dataset.tin || '—'}`;

            fixOutOverlay.classList.add('active');
            fixOutOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });

        document.getElementById('closeFixOut')?.addEventListener('click', closeFixOut);
        document.getElementById('cancelFixOut')?.addEventListener('click', closeFixOut);
        fixOutOverlay?.addEventListener('click', (e) => {
            if (e.target === fixOutOverlay) closeFixOut();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && fixOutOverlay.classList.contains('active')) closeFixOut();
        });

        // ── Clear All Attendance: custom confirm modal ──
        const clearAllOverlay = document.getElementById('clearAllOverlay');
        const openClearAllBtn = document.getElementById('openClearAllBtn');
        const cancelClearAllBtn = document.getElementById('cancelClearAll');
        const confirmClearAllBtn = document.getElementById('confirmClearAllBtn');
        const clearAllForm = document.getElementById('clearAllForm');

        function openClearAllModal() {
            clearAllOverlay.classList.add('active');
            clearAllOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeClearAllModal() {
            clearAllOverlay.classList.remove('active');
            clearAllOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        openClearAllBtn?.addEventListener('click', openClearAllModal);
        cancelClearAllBtn?.addEventListener('click', closeClearAllModal);
        clearAllOverlay?.addEventListener('click', (e) => {
            if (e.target === clearAllOverlay) closeClearAllModal();
        });

        // Only this click actually submits the form. A plain click on
        // "Clear All" in the toolbar just opens the modal above.
        confirmClearAllBtn?.addEventListener('click', () => {
            confirmClearAllBtn.disabled = true;
            confirmClearAllBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting&hellip;';
            clearAllForm.submit();
        });

        // ===== SEARCH, FILTER & PAGINATION (main Attendance table) =====
        (function() {
            const searchInput = document.getElementById('employeeSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            const areaFilter = document.getElementById('areaFilter');
            const lateToggle = document.getElementById('filterLate');
            const otToggle = document.getElementById('filterOvertime');
            const clearBtn = document.getElementById('filterClear');
            let noMatchRow = document.getElementById('noMatchRow');
            const paginationEl = document.getElementById('pagination');
            let tbody = document.querySelector('.table-box table tbody');
            if (!tbody) return;

            const PAGE_SIZE = 10;
            let currentPage = 1;

            let groups = [];

            // Rebuilt every time the table is replaced (live update).
            function rebuildGroups() {
                tbody = document.querySelector('.table-box table tbody');
                noMatchRow = document.getElementById('noMatchRow');
                groups = [];
                if (!tbody) return;

                let current = null;
                Array.from(tbody.children).forEach(function(tr) {
                    if (tr.classList.contains('category-row')) {
                        current = { catRow: tr, rows: [] };
                        groups.push(current);
                    } else if (current && tr.dataset.name !== undefined) {
                        current.rows.push(tr);
                    }
                });
            }
            rebuildGroups();

            function getMatches() {
                const q = (searchInput.value || '').trim().toLowerCase();
                const cat = categoryFilter.value || '';
                const area = areaFilter.value || '';
                const wantLate = lateToggle.classList.contains('active');
                const wantOT = otToggle.classList.contains('active');
                const matches = [];
                groups.forEach(g => g.rows.forEach(row => {
                    const ok = (!q || row.dataset.name.indexOf(q) !== -1) &&
                        (!cat || row.dataset.category === cat) &&
                        (!area || row.dataset.area === area) &&
                        (!wantLate || row.dataset.late === '1') &&
                        (!wantOT || row.dataset.overtime === '1');
                    if (ok) matches.push(row);
                }));
                return matches;
            }

            function renderPagination(totalPages) {
                if (totalPages <= 1) {
                    paginationEl.style.display = 'none';
                    paginationEl.innerHTML = '';
                    return;
                }
                paginationEl.style.display = 'flex';
                let html = `<span class="pagination-info">Page ${currentPage} of ${totalPages}</span>`;
                html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">&laquo; Prev</button>`;
                for (let p = 1; p <= totalPages; p++) {
                    html += `<button class="${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
                html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">Next &raquo;</button>`;
                paginationEl.innerHTML = html;
                paginationEl.querySelectorAll('button[data-page]').forEach(b => {
                    b.addEventListener('click', () => {
                        currentPage = parseInt(b.dataset.page, 10);
                        render();
                    });
                });
            }

            function render() {
                const matches = getMatches();
                const totalPages = Math.max(1, Math.ceil(matches.length / PAGE_SIZE));
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);
                const visible = matches.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);
                renderPagination(totalPages);

                const visibleSet = new Set(visible);
                groups.forEach(g => {
                    let groupVisible = false;
                    g.rows.forEach(row => {
                        const show = visibleSet.has(row);
                        row.style.display = show ? '' : 'none';
                        if (show) groupVisible = true;
                    });
                    g.catRow.style.display = groupVisible ? '' : 'none';
                });

                if (noMatchRow) noMatchRow.style.display = matches.length ? 'none' : '';
            }

            function applyFilters() {
                currentPage = 1;
                render();
            }

            searchInput.addEventListener('input', applyFilters);
            categoryFilter.addEventListener('change', applyFilters);
            areaFilter.addEventListener('change', applyFilters);
            lateToggle.addEventListener('click', () => {
                lateToggle.classList.toggle('active');
                applyFilters();
            });
            otToggle.addEventListener('click', () => {
                otToggle.classList.toggle('active');
                applyFilters();
            });
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                categoryFilter.value = '';
                areaFilter.value = '';
                lateToggle.classList.remove('active');
                otToggle.classList.remove('active');
                applyFilters();
            });

            // Called by the live update after the table is replaced.
            // The search, filters and page number are preserved.
            window.attendanceRefresh = function() {
                rebuildGroups();
                render();
            };

            render();
        })();

        // ===== LIVE UPDATE: the sheet refreshes itself whenever someone Times In / Times Out =====
        (function() {
            const SIG_URL = "{{ route('attendance.live') }}?period={{ $periodStart->format('Y-m-d') }}";
            const PAGE_URL = "{{ route('attendance') }}?period={{ $periodStart->format('Y-m-d') }}";
            const POLL_MS = 5000;

            let lastSig = null;
            let busy = false;

            async function refreshSheet() {
                const res = await fetch(PAGE_URL, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                });
                if (!res.ok) return false;

                const html = await res.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');

                const newTable = doc.querySelector('.table-box table');
                const oldTable = document.querySelector('.table-box table');
                if (newTable && oldTable) {
                    oldTable.parentNode.replaceChild(document.importNode(newTable, true), oldTable);
                }

                const newStats = doc.querySelector('.stats-row');
                const oldStats = document.querySelector('.stats-row');
                if (newStats && oldStats) {
                    oldStats.innerHTML = newStats.innerHTML;
                }

                if (window.attendanceRefresh) window.attendanceRefresh();
                return true;
            }

            async function check() {
                if (document.hidden || busy) return;
                busy = true;

                try {
                    const res = await fetch(SIG_URL, {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store',
                    });
                    if (!res.ok) return;

                    const data = await res.json();

                    // First call: just record the current state.
                    if (lastSig === null) {
                        lastSig = data.sig;
                        return;
                    }

                    if (data.sig !== lastSig) {
                        const ok = await refreshSheet();
                        if (ok) {
                            lastSig = data.sig;
                            showToast('New attendance recorded. The sheet has been updated.', 'success', {
                                title: 'Live update',
                                duration: 2500
                            });
                        }
                    }
                } catch (e) {
                    // Brief network loss or the server restarted; try again on the next poll.
                } finally {
                    busy = false;
                }
            }

            setInterval(check, POLL_MS);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) check();
            });
            check();
        })();
    </script>
@endpush