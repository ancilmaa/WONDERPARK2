@extends('layouts.sidebar')

@section('title', 'Attendance')

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
    <style>
        .serif {
            font-family: 'Source Serif 4', serif;
        }

        .toolbar {
            background: var(--card);
            padding: 22px 26px;
            margin-bottom: 20px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .toolbar .eyebrow {
            font-size: .68rem;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: 6px;
        }

        .toolbar h2 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar-actions input[type="file"] {
            font-size: 12px;
            color: var(--ink-soft);
            padding: 9px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            background: var(--bg);
            max-width: 210px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
        }

        .stat-card .label {
            font-size: .68rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 9px;
        }

        .stat-card .value {
            font-family: 'Source Serif 4', serif;
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1;
        }

        .stat-card .sub {
            font-size: 11px;
            color: var(--muted);
            margin-top: 7px;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--line);
        }

        .filter-bar .filter-label {
            font-size: .68rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-right: 2px;
        }

        .filter-bar input[type="text"],
        .filter-bar select {
            padding: 9px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 12.5px;
            font-family: 'Inter', sans-serif;
            color: var(--ink-soft);
            background: var(--bg);
        }

        .filter-bar input[type="text"] {
            flex: 1;
            min-width: 180px;
            max-width: 280px;
        }

        .filter-bar select {
            cursor: pointer;
        }

        .filter-toggle {
            padding: 9px 14px;
            border: 1px solid var(--line-strong);
            border-radius: 999px;
            background: #fff;
            color: var(--ink-soft);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s ease;
            font-family: 'Inter', sans-serif;
        }

        .filter-toggle:hover {
            border-color: var(--pink);
        }

        .filter-toggle.active[data-type="late"] {
            background: var(--deduct);
            border-color: var(--deduct);
            color: #fff;
        }

        .filter-toggle.active[data-type="overtime"] {
            background: var(--present);
            border-color: var(--present);
            color: #fff;
        }

        .filter-clear {
            background: none;
            border: none;
            color: var(--pink-deep);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            font-family: 'Inter', sans-serif;
            padding: 9px 4px;
        }

        .pagination {
            display: none;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 6px;
            padding-top: 18px;
            margin-top: 4px;
            border-top: 1px solid var(--line);
        }

        .pagination-info {
            font-size: 11.5px;
            color: var(--muted);
            margin-right: 6px;
        }

        .pagination button {
            min-width: 30px;
            padding: 7px 10px;
            border: 1px solid var(--line-strong);
            background: #fff;
            color: var(--ink-soft);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        }

        .pagination button.active {
            background: var(--pink-deep);
            border-color: var(--pink-deep);
            color: #fff;
        }

        .pagination button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        #noMatchRow td {
            padding: 26px !important;
            color: var(--muted) !important;
            background: var(--card) !important;
        }

        .table-box {
            background: var(--card);
            padding: 28px 26px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

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

        .payroll-header h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 19px;
            font-weight: 700;
            letter-spacing: 0.6px;
            color: var(--ink);
        }

        .payroll-header p {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
            letter-spacing: 0.2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px;
            font-size: 12px;
            font-variant-numeric: tabular-nums;
        }

        th,
        td {
            padding: 11px 6px;
            text-align: center;
            border-bottom: 1px solid var(--line);
        }

        thead tr:last-child th {
            background: var(--ink);
            color: #fff;
            font-weight: 600;
            font-size: 11px;
            padding: 10px 6px;
            border-bottom: none;
        }

        .week-end {
            border-right: 1px solid var(--line-strong) !important;
        }

        tbody tr:hover:not(.category-row):not(.total-row) {
            background: var(--pink-pale);
        }

        td {
            color: var(--ink-soft);
        }

        td:nth-child(2) {
            text-align: left;
            color: var(--ink);
            font-weight: 600;
        }

        .category-row td {
            background: var(--pink-light);
            color: var(--pink-deep);
            font-weight: 700;
            text-align: left;
            padding: 8px 14px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-size: 10.5px;
            border-top: 1px solid var(--line-strong);
            border-bottom: 1px solid var(--line-strong);
        }

        .cell-present {
            color: var(--present);
            font-weight: 700;
        }

        .cell-deduction {
            color: var(--deduct);
            font-weight: 600;
            font-size: 10.5px;
        }

        .cell-rest {
            color: var(--rest);
            font-weight: 400;
        }

        .days-worked {
            background: var(--pink-pale);
            color: var(--ink);
            font-weight: 700;
            font-family: 'Source Serif 4', serif;
            font-size: 13px;
        }

        .total-row td {
            background: transparent;
            color: var(--ink);
            font-weight: 700;
            font-size: 13.5px;
            border-top: 3px double var(--ink);
            border-bottom: none;
            padding-top: 16px;
        }

        .total-row td:last-child {
            font-family: 'Source Serif 4', serif;
            font-size: 16px;
            color: var(--pink-deep);
        }

        .print-btn {
            padding: 11px 20px;
            background: var(--pink-deep);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            transition: background .15s ease, box-shadow .15s ease;
        }

        .print-btn:hover {
            background: var(--pink-dark);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        #uploadBtn {
            padding: 11px 18px !important;
            background: #fff !important;
            color: var(--ink) !important;
            border: 1px solid var(--line-strong) !important;
            border-radius: 10px !important;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            transition: background .15s ease, border-color .15s ease;
        }

        #uploadBtn:hover {
            background: var(--bg) !important;
            border-color: var(--pink) !important;
        }

        #toastStack {
            position: fixed;
            top: 22px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 420px;
            padding: 0 16px;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            width: 100%;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff;
            color: var(--ink);
            padding: 14px 16px;
            border-radius: 14px;
            border-left: 4px solid var(--ink);
            box-shadow: var(--shadow-md);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
            opacity: 0;
            transform: translateY(-16px) scale(.97);
            animation: toastIn .35s cubic-bezier(.34, 1.56, .64, 1) forwards;
        }

        .toast.hide {
            animation: toastOut .28s ease forwards;
        }

        .toast .toast-icon {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #fff;
            background: var(--ink-soft);
        }

        .toast.toast-info .toast-icon {
            background: var(--pink-deep);
        }

        .toast.toast-success {
            border-left-color: var(--present);
        }

        .toast.toast-success .toast-icon {
            background: var(--present);
        }

        .toast.toast-error {
            border-left-color: var(--deduct);
        }

        .toast.toast-error .toast-icon {
            background: var(--deduct);
        }

        .toast.toast-info {
            border-left-color: var(--pink-deep);
        }

        .toast .toast-body {
            flex: 1;
            min-width: 0;
            padding-top: 2px;
        }

        .toast .toast-title {
            font-weight: 700;
            font-size: 12.5px;
            margin-bottom: 2px;
            color: var(--ink);
        }

        .toast .toast-msg {
            color: var(--ink-soft);
            font-size: 12.5px;
            word-break: break-word;
        }

        .toast .toast-close {
            flex-shrink: 0;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            font-size: 13px;
            padding: 2px;
            line-height: 1;
            margin-top: 1px;
        }

        .toast .toast-close:hover {
            color: var(--ink);
        }

        .toast .toast-bar {
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            border-radius: 0 0 0 14px;
            background: currentColor;
            opacity: .35;
            animation: toastShrink 3s linear forwards;
        }

        .toast {
            position: relative;
            overflow: hidden;
        }

        @keyframes toastIn {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes toastOut {
            to {
                opacity: 0;
                transform: translateY(-12px) scale(.96);
            }
        }

        @keyframes toastShrink {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        @media(max-width:520px) {
            #toastStack {
                top: 14px;
                max-width: calc(100% - 24px);
                padding: 0;
            }
        }

        #uploadOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            align-items: center;
            justify-content: center;
            background: rgba(26, 21, 35, .38);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            animation: overlayFade .2s ease;
        }

        #uploadOverlay.active {
            display: flex;
        }

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

        .upload-card .upload-title {
            font-family: 'Source Serif 4', serif;
            font-weight: 700;
            font-size: 16px;
            color: var(--ink);
        }

        .upload-card .upload-sub {
            font-size: 12.5px;
            color: var(--ink-soft);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes overlayFade {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes cardPop {
            from {
                opacity: 0;
                transform: scale(.92);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        @media(max-width:900px) {
            .stats-row {
                grid-template-columns: 1fr 1fr;
            }

            .toolbar h2 {
                font-size: 1.05rem;
            }

            .toolbar-actions {
                width: 100%;
            }

            .toolbar-actions input[type="file"] {
                max-width: none;
                width: 100%;
            }

            .toolbar-actions button {
                width: 100%;
            }

            table {
                min-width: 760px;
                font-size: 11px;
            }

            th,
            td {
                padding: 8px 4px;
            }

            .filter-bar input[type="text"] {
                max-width: none;
                width: 100%;
            }

            .filter-bar select {
                width: 100%;
            }
        }

        @media(max-width:520px) {
            table {
                min-width: 640px;
                font-size: 10px;
            }

            .payroll-header h3 {
                font-size: 14px;
            }

            .payroll-header p {
                font-size: 11px;
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .app-shell {
                display: block;
            }

            .sidebar,
            .sidebar-overlay,
            .mobile-topbar,
            .toolbar,
            .stats-row,
            .no-print {
                display: none !important;
            }

            .content-body {
                padding: 0;
                max-width: none;
            }

            .table-box {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                overflow: visible;
            }

            table {
                min-width: unset;
                width: 100%;
                font-size: 10px;
            }

            th,
            td {
                border: 1px solid #999;
                padding: 4px;
            }

            .cell-present {
                color: var(--present) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .cell-deduction {
                color: var(--deduct) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .category-row td {
                background: var(--pink-light) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .days-worked {
                background: var(--pink-pale) !important;
                color: var(--ink) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            thead tr:last-child th {
                background: var(--ink) !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $totalEmployees = isset($records) ? $records->count() : 0;
        $grouped = isset($records) ? $records->groupBy('category') : collect();
        $totalCategories = $grouped->count();

        $totalDaysAll = 0;
        if (isset($records)) {
            foreach ($records as $rec) {
                $d = 0;
                for ($i = 1; $i <= 15; $i++) {
                    $v = $rec->{'day_' . $i} ?? '';
                    if ($v === 'P' || str_starts_with((string) $v, '+')) {
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
            <h2>Bio Attendance Sheet</h2>
        </div>

        <div class="toolbar-actions">
            <form id="uploadForm" action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data"
                style="display:contents;">
                @csrf
                <input type="file" name="import_file" required>
                <button id="uploadBtn" type="submit">Import Bio File</button>
            </form>
            <button class="print-btn" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="stats-row no-print">
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
                @foreach ($grouped->keys() as $cat)
                    <option value="{{ strtolower($cat) }}">{{ ucwords(strtolower($cat)) }}</option>
                @endforeach
            </select>
            <button type="button" class="filter-toggle" data-type="late" id="filterLate">Late / Absent</button>
            <button type="button" class="filter-toggle" data-type="overtime" id="filterOvertime">Overtime</button>
            <button type="button" class="filter-clear" id="filterClear">Clear filters</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th colspan="2" style="text-align:left;padding-left:14px;">Name of Employee</th>
                    @for ($i = 1; $i <= 15; $i++)
                        <th class="{{ $i == 5 || $i == 10 ? 'week-end' : '' }}">{{ $i }}</th>
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

                    @foreach ($grouped as $category => $employees)
                        <tr class="category-row" data-cat-row="{{ strtolower($category) }}">
                            <td colspan="19">{{ strtoupper($category) }}</td>
                        </tr>

                        @foreach ($employees as $item)
                            @php
                                $daysWorked = 0;
                                $hasOvertime = false;
                                $hasLate = false;
                                for ($i = 1; $i <= 15; $i++) {
                                    $v = $item->{'day_' . $i} ?? '';
                                    if ($v === 'P' || str_starts_with((string) $v, '+')) {
                                        $daysWorked++;
                                    }
                                    if (str_starts_with((string) $v, '+')) {
                                        $hasOvertime = true;
                                    }
                                    if (str_starts_with((string) $v, '-')) {
                                        $hasLate = true;
                                    }
                                }
                            @endphp
                            <tr data-name="{{ strtolower($item->name) }}" data-category="{{ strtolower($category) }}"
                                data-late="{{ $hasLate ? 1 : 0 }}" data-overtime="{{ $hasOvertime ? 1 : 0 }}">
                                <td>{{ $no++ }}</td>
                                <td colspan="2">{{ $item->name }}</td>

                                @for ($i = 1; $i <= 15; $i++)
                                    @php
                                        $val = $item->{'day_' . $i} ?? '';
                                        $weekEndClass = $i == 5 || $i == 10 ? 'week-end' : '';
                                    @endphp

                                    <td class="{{ $weekEndClass }}">
                                        @if ($val === 'P' || str_starts_with((string) $val, '+'))
                                            <span class="cell-present">{{ $val === 'P' ? 'P' : $val }}</span>
                                        @elseif(str_starts_with((string) $val, '-'))
                                            <span class="cell-deduction">{{ $val }}</span>
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
                        <td colspan="19">No employees match your search or filters.</td>
                    </tr>

                    <tr class="total-row">
                        <td colspan="18" style="text-align:center;">
                            TOTAL SALARY FOR {{ strtoupper($latestUpload->branch ?? 'DINO PLAY, THE OUTLET AT LIPA') }}
                        </td>
                        <td>{{ $totalDays }}.00</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="19" style="padding:24px;color:var(--muted);">No attendance data found. Please import
                            a BIO file.</td>
                    </tr>
                @endif

            </tbody>
        </table>

        <div class="pagination no-print" id="pagination"></div>

    </div>

    <div id="toastStack" class="no-print" aria-live="polite"></div>

    <div id="uploadOverlay" class="no-print" aria-live="assertive">
        <div class="upload-card">
            <div class="upload-spinner"></div>
            <div class="upload-title">Uploading BIO file</div>
            <div class="upload-sub">Please wait, this won't take long&hellip;</div>
        </div>
    </div>

    @if (session('success'))
        <span id="flash-success" data-msg="{{ session('success') }}" style="display:none;"></span>
    @endif

    @if (session('error'))
        <span id="flash-error" data-msg="{{ session('error') }}" style="display:none;"></span>
    @endif

@endsection

@push('scripts')
    <script>
        const form = document.getElementById("uploadForm");
        const btn = document.getElementById("uploadBtn");
        const toastStack = document.getElementById("toastStack");
        const uploadOverlay = document.getElementById("uploadOverlay");

        const TOAST_ICONS = {
            info: {
                icon: 'fa-solid fa-arrow-up-from-bracket',
                title: 'Uploading'
            },
            success: {
                icon: 'fa-solid fa-circle-check',
                title: 'Success'
            },
            error: {
                icon: 'fa-solid fa-circle-exclamation',
                title: 'Error'
            }
        };

        function showToast(msg, type = "info", opts = {}) {
            const cfg = TOAST_ICONS[type] || TOAST_ICONS.info;
            const title = opts.title || cfg.title;
            const duration = opts.duration ?? 3000;

            const el = document.createElement('div');
            el.className = `toast toast-${type}`;
            el.innerHTML = `
        <div class="toast-icon"><i class="${cfg.icon}"></i></div>
        <div class="toast-body">
            <div class="toast-title">${title}</div>
            <div class="toast-msg"></div>
        </div>
        <button type="button" class="toast-close" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>
        <div class="toast-bar" style="animation-duration:${duration}ms;"></div>
    `;
            el.querySelector('.toast-msg').textContent = msg;

            function dismiss() {
                if (!el.isConnected) return;
                el.classList.add('hide');
                setTimeout(() => el.remove(), 280);
            }

            el.querySelector('.toast-close').addEventListener('click', dismiss);
            toastStack.appendChild(el);

            if (duration > 0) {
                setTimeout(dismiss, duration);
            }

            return {
                el,
                dismiss
            };
        }

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

        const successMsg = document.getElementById('flash-success')?.dataset.msg;
        const errorMsg = document.getElementById('flash-error')?.dataset.msg;

        if (successMsg) showToast(successMsg, "success");
        if (errorMsg) showToast(errorMsg, "error");

        // ===== SEARCH, FILTER & PAGINATION =====
        (function() {
            const searchInput = document.getElementById('employeeSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            const lateToggle = document.getElementById('filterLate');
            const otToggle = document.getElementById('filterOvertime');
            const clearBtn = document.getElementById('filterClear');
            const noMatchRow = document.getElementById('noMatchRow');
            const paginationEl = document.getElementById('pagination');
            const tbody = document.querySelector('.table-box table tbody');
            if (!tbody) return;

            const PAGE_SIZE = 10;
            let currentPage = 1;

            const groups = [];
            let current = null;
            Array.from(tbody.children).forEach(function(tr) {
                if (tr.classList.contains('category-row')) {
                    current = {
                        catRow: tr,
                        rows: []
                    };
                    groups.push(current);
                } else if (current && tr.dataset.name !== undefined) {
                    current.rows.push(tr);
                }
            });

            function getMatches() {
                const q = (searchInput.value || '').trim().toLowerCase();
                const cat = categoryFilter.value || '';
                const wantLate = lateToggle.classList.contains('active');
                const wantOT = otToggle.classList.contains('active');
                const matches = [];
                groups.forEach(g => g.rows.forEach(row => {
                    const ok = (!q || row.dataset.name.indexOf(q) !== -1) &&
                        (!cat || row.dataset.category === cat) &&
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
                html +=
                    `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">&laquo; Prev</button>`;
                for (let p = 1; p <= totalPages; p++) {
                    html += `<button class="${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
                html +=
                    `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">Next &raquo;</button>`;
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
                lateToggle.classList.remove('active');
                otToggle.classList.remove('active');
                applyFilters();
            });

            render();
        })();
    </script>
@endpush
