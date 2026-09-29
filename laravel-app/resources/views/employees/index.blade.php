@extends('layouts.sidebar')

@section('title', 'Employees')

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
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--ink);
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--ink);
            line-height: 1;
        }

        .stat-card .value.present { color: var(--present); }
        .stat-card .value.deduct { color: var(--deduct); }

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
            margin-bottom: 14px;
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

        .filter-toggle:hover { border-color: var(--pink); }

        .filter-toggle[data-type="active"].active {
            background: var(--present);
            border-color: var(--present);
            color: #fff;
        }

        .filter-toggle[data-type="inactive"].active {
            background: var(--deduct);
            border-color: var(--deduct);
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

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid var(--line);
        }

        thead tr {
            background: var(--ink);
        }

        thead th {
            color: #fff;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: 12px 10px;
            border-bottom: none;
        }

        tbody tr:hover {
            background: var(--pink-pale);
        }

        td.emp-name-cell {
            font-weight: 700;
            color: var(--ink);
        }

        .badge-category {
            font-size: .74rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .badge-category--manager { background: var(--pink-light); color: var(--pink-deep); }
        .badge-category--team-leader { background: #E3F0FF; color: #2563EB; }
        .badge-category--staff { background: var(--amber-light); color: var(--amber); }

        .badge-status {
            font-size: .74rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .badge-status--active { background: var(--present-soft); color: var(--present); }
        .badge-status--inactive { background: var(--deduct-soft); color: var(--deduct); }

        .row-actions {
            display: flex;
            gap: 8px;
        }

        .row-actions form { display: inline; }

        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            transition: .15s;
        }

        .btn-icon.edit { background: var(--bg); color: var(--ink-soft); }
        .btn-icon.edit:hover { background: var(--pink-light); color: var(--pink-deep); }
        .btn-icon.deactivate { background: var(--deduct-soft); color: var(--deduct); }
        .btn-icon.deactivate:hover { background: var(--deduct); color: #fff; }
        .btn-icon.activate { background: var(--present-soft); color: var(--present); }
        .btn-icon.activate:hover { background: var(--present); color: #fff; }

        .btn-ghost {
            padding: 11px 18px;
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            transition: background .15s ease, border-color .15s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-ghost:hover {
            background: var(--bg);
            border-color: var(--pink);
        }

        .btn-primary {
            padding: 11px 20px;
            background: var(--pink-deep);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            transition: background .15s ease, box-shadow .15s ease;
        }

        .btn-primary:hover {
            background: var(--pink-dark);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        .btn-danger {
            padding: 11px 20px;
            background: var(--deduct);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            transition: background .15s ease, box-shadow .15s ease;
        }

        .btn-danger:hover {
            filter: brightness(0.92);
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
            position: relative;
            overflow: hidden;
        }

        .toast.hide { animation: toastOut .28s ease forwards; }

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

        .toast.toast-success { border-left-color: var(--present); }
        .toast.toast-success .toast-icon { background: var(--present); }
        .toast.toast-error { border-left-color: var(--deduct); }
        .toast.toast-error .toast-icon { background: var(--deduct); }

        .toast .toast-body { flex: 1; min-width: 0; padding-top: 2px; }
        .toast .toast-title { font-weight: 700; font-size: 12.5px; margin-bottom: 2px; color: var(--ink); }
        .toast .toast-msg { color: var(--ink-soft); font-size: 12.5px; word-break: break-word; }

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

        .toast .toast-close:hover { color: var(--ink); }

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

        @keyframes toastIn { to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity: 0; transform: translateY(-12px) scale(.96); } }
        @keyframes toastShrink { from { width: 100%; } to { width: 0%; } }

        #addEmployeeOverlay, #editEmployeeOverlay, #importOverlay, #confirmOverlay, #clearAllOverlay {
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
            padding: 20px;
        }

        #addEmployeeOverlay.active, #editEmployeeOverlay.active, #importOverlay.active, #confirmOverlay.active, #clearAllOverlay.active {
            display: flex;
        }

        .modal-card {
            background: #fff;
            border-radius: 18px;
            width: 100%;
            max-width: 480px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            box-shadow: var(--shadow-md);
            animation: cardPop .25s cubic-bezier(.34, 1.56, .64, 1);
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 22px 24px 18px;
            border-bottom: 1px solid var(--line);
        }

        .modal-head .eyebrow {
            font-size: .66rem;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: 4px;
        }

        .modal-head h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ink);
        }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            border: none;
            background: var(--bg);
            color: var(--ink-soft);
            font-size: 14px;
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover { background: var(--pink-light); color: var(--pink-deep); }

        .modal-body { padding: 22px 24px; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }

        .form-group label {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--ink-soft);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .form-group input, .form-group select {
            padding: 10px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: var(--bg);
        }

        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--pink);
            background: #fff;
        }

        .form-group input#addPinCode,
        .form-group input#editPinCode {
            letter-spacing: 6px;
            font-weight: 700;
        }

        .form-hint { font-size: 11px; color: var(--muted); }

        .modal-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 18px 24px 24px;
        }

        /* ── Confirm modal (replaces native browser confirm()) ── */
        .confirm-card {
            max-width: 380px;
            text-align: center;
        }

        .confirm-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin: 26px auto 4px;
        }

        .confirm-icon.warn {
            background: var(--deduct-soft);
            color: var(--deduct);
        }

        .confirm-icon.info {
            background: var(--present-soft);
            color: var(--present);
        }

        .confirm-body {
            padding: 4px 24px 22px;
        }

        .confirm-title {
            font-family: 'Source Serif 4', serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .confirm-message {
            font-size: 13px;
            color: var(--ink-soft);
            line-height: 1.5;
            text-align: center;
        }

        .confirm-foot {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 0 24px 26px;
        }

        .confirm-foot button {
            flex: 1;
        }

        @keyframes overlayFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes cardPop { from { opacity: 0; transform: scale(.92); } to { opacity: 1; transform: scale(1); } }

        @media(max-width:900px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
            .toolbar h2 { font-size: 1rem; }
            .toolbar-actions { width: 100%; }
            .toolbar-actions button { width: 100%; }
            .filter-bar input[type="text"] { max-width: none; width: 100%; }
            .filter-bar select { width: 100%; }
            .form-grid { grid-template-columns: 1fr; }
            table { min-width: 640px; }
            .table-box { overflow-x: auto; }
        }
    </style>
@endsection

@section('content')

<div class="toolbar no-print">
    <div>
        <div class="eyebrow">Lipa Branch &middot; Employee Records</div>
        <h2>Employee Management</h2>
        <p class="sub" style="font-size:12px;color:var(--muted);margin-top:4px;">
            Manage your team's records, categories, and status.
        </p>
    </div>

    <div class="toolbar-actions">
        <button type="button" class="btn-ghost" id="openImportBtn">
            <i class="fa-solid fa-file-import"></i> Import CSV
        </button>
        <button type="button" class="btn-ghost" id="openClearAllBtn" style="color:var(--deduct);border-color:var(--deduct-soft);">
            <i class="fa-solid fa-trash"></i> Clear All
        </button>
        <button type="button" class="btn-primary" id="openAddBtn">
            <i class="fa-solid fa-user-plus"></i> Add Employee
        </button>
    </div>
</div>

<div class="stats-row no-print">
    <div class="stat-card">
        <div class="label">Total Employees</div>
        <div class="value">{{ $totalCount }}</div>
        <div class="sub">Currently on record</div>
    </div>
    <div class="stat-card">
        <div class="label">Active</div>
        <div class="value present">{{ $activeCount }}</div>
        <div class="sub">Currently working</div>
    </div>
    <div class="stat-card">
        <div class="label">Inactive</div>
        <div class="value deduct">{{ $inactiveCount }}</div>
        <div class="sub">Deactivated records</div>
    </div>
</div>

<div class="table-box">

    <div class="filter-bar no-print">
        <span class="filter-label">Filter</span>
        <input type="text" id="empSearch" placeholder="Search employee name&hellip;">
        <select id="empCategoryFilter">
            <option value="">All Categories</option>
            <option value="Manager">Manager</option>
            <option value="Team Leader">Team Leader</option>
            <option value="Staff">Staff</option>
        </select>
        <select id="empMonthFilter">
            <option value="">All Months (Hired)</option>
            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
        </select>
        <select id="empSortBy">
            <option value="name">Sort: Name (A–Z)</option>
            <option value="tenure_desc">Sort: Longest Tenure First</option>
            <option value="tenure_asc">Sort: Most Recently Hired</option>
        </select>
        <button type="button" class="filter-toggle" data-type="active" id="filterActiveOnly">Active</button>
        <button type="button" class="filter-toggle" data-type="inactive" id="filterInactiveOnly">Inactive</button>
        <button type="button" class="filter-clear" id="filterClear">Clear filters</button>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:44px;">NO</th>
                <th>Name of Employee</th>
                <th>Category</th>
                <th>Status</th>
                <th>Date Hired</th>
                <th style="width:90px;">Actions</th>
            </tr>
        </thead>
        <tbody id="empTableBody">
            @forelse ($employees as $index => $employee)
                <tr data-name="{{ strtolower($employee->employee_name) }}"
                    data-category="{{ $employee->category }}"
                    data-status="{{ $employee->status }}"
                    data-date-hired="{{ $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('Y-m-d') : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="emp-name-cell">{{ $employee->employee_name }}</td>
                    <td>
                        <span class="badge-category badge-category--{{ Str::slug($employee->category) }}">{{ $employee->category }}</span>
                    </td>
                    <td>
                        <span class="badge-status badge-status--{{ $employee->status }}">{{ ucfirst($employee->status) }}</span>
                    </td>
                    <td>{{ $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('M d, Y') : '—' }}</td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="btn-icon edit" onclick='openEditModal(@json($employee))'>
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            @if ($employee->status === 'active')
                                <form action="{{ route('employees.deactivate', $employee->id) }}" method="POST" id="deactivateForm{{ $employee->id }}">
                                    @csrf
                                </form>
                                <button type="button" class="btn-icon deactivate"
                                    onclick="openConfirmModal({
                                        title: 'Deactivate employee?',
                                        message: 'This will remove {{ addslashes($employee->employee_name) }} from the Manual Entry and QR Check-in employee lists. Their attendance history will remain on record.',
                                        confirmLabel: 'Deactivate',
                                        variant: 'warn',
                                        formId: 'deactivateForm{{ $employee->id }}'
                                    })">
                                    <i class="fa-solid fa-user-slash"></i>
                                </button>
                            @else
                                <form action="{{ route('employees.activate', $employee->id) }}" method="POST" id="activateForm{{ $employee->id }}">
                                    @csrf
                                </form>
                                <button type="button" class="btn-icon activate"
                                    onclick="openConfirmModal({
                                        title: 'Reactivate employee?',
                                        message: 'This will restore {{ addslashes($employee->employee_name) }} to the Manual Entry and QR Check-in employee lists.',
                                        confirmLabel: 'Reactivate',
                                        variant: 'info',
                                        formId: 'activateForm{{ $employee->id }}'
                                    })">
                                    <i class="fa-solid fa-user-check"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:24px;color:var(--muted);text-align:center;">No employees found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div id="noMatchRow" style="display:none;padding:26px;color:var(--muted);text-align:center;">
        No employees match your search or filters.
    </div>

    <div class="pagination no-print" id="pagination"></div>

</div>

<div id="toastStack" class="no-print" aria-live="polite"></div>

{{-- Add Employee Modal --}}
<div id="addEmployeeOverlay" class="no-print" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true">
        <form method="POST" action="{{ route('employees.store') }}">
            @csrf
            <div class="modal-head">
                <div>
                    <div class="eyebrow">New Record</div>
                    <h3>Add Employee</h3>
                </div>
                <button type="button" class="modal-close" id="closeAddModal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Employee Name</label>
                        <input type="text" name="employee_name" required maxlength="255">
                    </div>
                    <div class="form-group full">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="">Select category</option>
                            <option value="Manager">Manager</option>
                            <option value="Team Leader">Team Leader</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Date Hired</label>
                        <input type="date" name="date_hired">
                    </div>
                    <div class="form-group full">
                        <label>PIN Code (optional)</label>
                        <input type="password" inputmode="numeric" pattern="[0-9]*" name="pin_code" id="addPinCode"
                            maxlength="4" autocomplete="off" placeholder="4-digit PIN, e.g. 1234">
                        <p class="form-hint">Used for QR self check-in. You can set this later instead.</p>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-ghost" id="cancelAddModal">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Add Employee</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Employee Modal --}}
<div id="editEmployeeOverlay" class="no-print" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true">
        <form id="editEmployeeForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Update Record</div>
                    <h3>Edit Employee</h3>
                </div>
                <button type="button" class="modal-close" id="closeEditModal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Employee Name</label>
                        <input type="text" name="employee_name" id="editName" required maxlength="255">
                    </div>
                    <div class="form-group full">
                        <label>Category</label>
                        <select name="category" id="editCategory" required>
                            <option value="Manager">Manager</option>
                            <option value="Team Leader">Team Leader</option>
                            <option value="Staff">Staff</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Date Hired</label>
                        <input type="date" name="date_hired" id="editDateHired">
                    </div>
                    <div class="form-group full">
                        <label>PIN Code</label>
                        <input type="password" inputmode="numeric" pattern="[0-9]*" name="pin_code" id="editPinCode"
                            maxlength="4" autocomplete="off" placeholder="Leave blank to keep current PIN">
                        <p class="form-hint">The current PIN is hashed and can't be shown here. Leave this blank to keep it unchanged, or enter 4 digits to replace it.</p>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-ghost" id="cancelEditModal">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Import CSV Modal --}}
<div id="importOverlay" class="no-print" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true">
        <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data" id="importForm">
            @csrf
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Bulk Add</div>
                    <h3>Import Employees (CSV)</h3>
                </div>
                <button type="button" class="modal-close" id="closeImportModal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group full">
                    <label>CSV File</label>
                    <input type="file" name="import_file" accept=".csv,.txt" required>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-ghost" id="cancelImportModal">Cancel</button>
                <button type="submit" class="btn-primary" id="importSubmitBtn"><i class="fa-solid fa-file-import"></i> Import</button>
            </div>
        </form>
    </div>
</div>

{{-- Custom Confirm Modal (replaces native browser confirm()) --}}
<div id="confirmOverlay" class="no-print" aria-hidden="true">
    <div class="modal-card confirm-card" role="alertdialog" aria-modal="true">
        <div class="confirm-icon" id="confirmIcon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="confirm-body">
            <div class="confirm-title" id="confirmTitle">Are you sure?</div>
            <div class="confirm-message" id="confirmMessage"></div>
        </div>
        <div class="confirm-foot">
            <button type="button" class="btn-ghost" id="confirmCancelBtn">Cancel</button>
            <button type="button" class="btn-danger" id="confirmOkBtn">Confirm</button>
        </div>
    </div>
</div>

{{-- Clear All Employees Modal — type-to-confirm because this is permanent --}}
<div id="clearAllOverlay" class="no-print" aria-hidden="true">
    <div class="modal-card confirm-card" role="alertdialog" aria-modal="true">
        <div class="confirm-icon warn"><i class="fa-solid fa-trash"></i></div>
        <div class="confirm-body">
            <div class="confirm-title">Clear all employees?</div>
            <div class="confirm-message">
                This will permanently delete ALL {{ $totalCount }} employee record(s) from the system.
                This does not affect existing Attendance or Payroll history.
                Use this before importing a fresh CSV with your complete employee list.
                <br><br>
                Type <strong>DELETE</strong> below to confirm.
            </div>
            <form action="{{ route('employees.clear-all') }}" method="POST" id="clearAllForm" style="margin-top:14px;">
                @csrf
                @method('DELETE')
                <input type="text" name="confirm_text" id="clearAllConfirmInput" placeholder="Type DELETE"
                    autocomplete="off"
                    style="width:100%;padding:10px 12px;border:1px solid var(--line-strong);border-radius:10px;font-size:13.5px;text-align:center;font-weight:700;letter-spacing:.05em;">
            </form>
        </div>
        <div class="confirm-foot">
            <button type="button" class="btn-ghost" id="clearAllCancelBtn">Cancel</button>
            <button type="button" class="btn-danger" id="clearAllOkBtn" disabled style="opacity:.5;">Delete All</button>
        </div>
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
    const toastStack = document.getElementById("toastStack");

    const TOAST_ICONS = {
        success: { icon: 'fa-solid fa-circle-check', title: 'Success' },
        error: { icon: 'fa-solid fa-circle-exclamation', title: 'Error' }
    };

    function showToast(msg, type = "success", opts = {}) {
        const cfg = TOAST_ICONS[type] || TOAST_ICONS.success;
        const title = opts.title || cfg.title;
        const duration = opts.duration ?? 4000;

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
        if (duration > 0) setTimeout(dismiss, duration);
    }

    const successMsg = document.getElementById('flash-success')?.dataset.msg;
    const errorMsg = document.getElementById('flash-error')?.dataset.msg;
    if (successMsg) showToast(successMsg, "success");
    if (errorMsg) showToast(errorMsg, "error");

    // Digits only, capped at 4 characters, for both PIN fields.
    function digitsOnly4(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
    }
    document.getElementById('addPinCode')?.addEventListener('input', digitsOnly4);
    document.getElementById('editPinCode')?.addEventListener('input', digitsOnly4);

    // ── Add Employee modal ──
    const addOverlay = document.getElementById('addEmployeeOverlay');
    document.getElementById('openAddBtn')?.addEventListener('click', () => {
        addOverlay.classList.add('active');
        addOverlay.setAttribute('aria-hidden', 'false');
    });
    function closeAdd() {
        addOverlay.classList.remove('active');
        addOverlay.setAttribute('aria-hidden', 'true');
    }
    document.getElementById('closeAddModal')?.addEventListener('click', closeAdd);
    document.getElementById('cancelAddModal')?.addEventListener('click', closeAdd);
    addOverlay?.addEventListener('click', (e) => { if (e.target === addOverlay) closeAdd(); });

    // ── Edit Employee modal ──
    const editOverlay = document.getElementById('editEmployeeOverlay');
    function openEditModal(employee) {
        document.getElementById('editName').value = employee.employee_name;
        document.getElementById('editCategory').value = employee.category;
        document.getElementById('editDateHired').value = employee.date_hired ? employee.date_hired.substring(0, 10) : '';
        // Always start blank — the stored value is a hash and can never be
        // shown back to the user. Leaving this blank on submit means
        // "keep the current PIN unchanged" (see EmployeeController@update).
        document.getElementById('editPinCode').value = '';
        document.getElementById('editEmployeeForm').action = '/employees/' + employee.id;
        editOverlay.classList.add('active');
        editOverlay.setAttribute('aria-hidden', 'false');
    }
    function closeEdit() {
        editOverlay.classList.remove('active');
        editOverlay.setAttribute('aria-hidden', 'true');
    }
    document.getElementById('closeEditModal')?.addEventListener('click', closeEdit);
    document.getElementById('cancelEditModal')?.addEventListener('click', closeEdit);
    editOverlay?.addEventListener('click', (e) => { if (e.target === editOverlay) closeEdit(); });

    // ── Import CSV modal ──
    const importOverlay = document.getElementById('importOverlay');
    document.getElementById('openImportBtn')?.addEventListener('click', () => {
        importOverlay.classList.add('active');
        importOverlay.setAttribute('aria-hidden', 'false');
    });
    function closeImport() {
        importOverlay.classList.remove('active');
        importOverlay.setAttribute('aria-hidden', 'true');
    }
    document.getElementById('closeImportModal')?.addEventListener('click', closeImport);
    document.getElementById('cancelImportModal')?.addEventListener('click', closeImport);
    importOverlay?.addEventListener('click', (e) => { if (e.target === importOverlay) closeImport(); });

    document.getElementById('importForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('importSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';
    });

    // ── Custom Confirm modal (replaces native browser confirm()) ──
    const confirmOverlay = document.getElementById('confirmOverlay');
    const confirmIcon = document.getElementById('confirmIcon');
    const confirmTitle = document.getElementById('confirmTitle');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmOkBtn = document.getElementById('confirmOkBtn');
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    let pendingFormId = null;

    function openConfirmModal({ title, message, confirmLabel, variant, formId }) {
        confirmTitle.textContent = title || 'Are you sure?';
        confirmMessage.textContent = message || '';
        confirmOkBtn.textContent = confirmLabel || 'Confirm';
        pendingFormId = formId;

        confirmIcon.classList.remove('warn', 'info');
        if (variant === 'info') {
            confirmIcon.classList.add('info');
            confirmIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
            confirmOkBtn.className = 'btn-primary';
        } else {
            confirmIcon.classList.add('warn');
            confirmIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
            confirmOkBtn.className = 'btn-danger';
        }

        confirmOverlay.classList.add('active');
        confirmOverlay.setAttribute('aria-hidden', 'false');
    }

    function closeConfirmModal() {
        confirmOverlay.classList.remove('active');
        confirmOverlay.setAttribute('aria-hidden', 'true');
        pendingFormId = null;
    }

    confirmOkBtn?.addEventListener('click', () => {
        if (pendingFormId) {
            const form = document.getElementById(pendingFormId);
            if (form) form.submit();
        }
        closeConfirmModal();
    });
    confirmCancelBtn?.addEventListener('click', closeConfirmModal);
    confirmOverlay?.addEventListener('click', (e) => { if (e.target === confirmOverlay) closeConfirmModal(); });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (addOverlay.classList.contains('active')) closeAdd();
            if (editOverlay.classList.contains('active')) closeEdit();
            if (importOverlay.classList.contains('active')) closeImport();
            if (confirmOverlay.classList.contains('active')) closeConfirmModal();
            if (clearAllOverlay.classList.contains('active')) closeClearAll();
        }
    });

    // ── Clear All Employees modal (type-to-confirm) ──
    const clearAllOverlay = document.getElementById('clearAllOverlay');
    const clearAllConfirmInput = document.getElementById('clearAllConfirmInput');
    const clearAllOkBtn = document.getElementById('clearAllOkBtn');
    const clearAllForm = document.getElementById('clearAllForm');

    document.getElementById('openClearAllBtn')?.addEventListener('click', () => {
        clearAllConfirmInput.value = '';
        clearAllOkBtn.disabled = true;
        clearAllOkBtn.style.opacity = '.5';
        clearAllOverlay.classList.add('active');
        clearAllOverlay.setAttribute('aria-hidden', 'false');
        setTimeout(() => clearAllConfirmInput.focus(), 100);
    });

    function closeClearAll() {
        clearAllOverlay.classList.remove('active');
        clearAllOverlay.setAttribute('aria-hidden', 'true');
    }

    document.getElementById('clearAllCancelBtn')?.addEventListener('click', closeClearAll);
    clearAllOverlay?.addEventListener('click', (e) => { if (e.target === clearAllOverlay) closeClearAll(); });

    clearAllConfirmInput?.addEventListener('input', function() {
        const isMatch = this.value.trim().toUpperCase() === 'DELETE';
        clearAllOkBtn.disabled = !isMatch;
        clearAllOkBtn.style.opacity = isMatch ? '1' : '.5';
    });

    clearAllOkBtn?.addEventListener('click', () => {
        if (clearAllConfirmInput.value.trim().toUpperCase() === 'DELETE') {
            clearAllForm.submit();
        }
    });

    // ===== SEARCH, FILTER, SORT & PAGINATION =====
    (function() {
        const searchInput = document.getElementById('empSearch');
        const categoryFilter = document.getElementById('empCategoryFilter');
        const monthFilter = document.getElementById('empMonthFilter');
        const sortBy = document.getElementById('empSortBy');
        const activeToggle = document.getElementById('filterActiveOnly');
        const inactiveToggle = document.getElementById('filterInactiveOnly');
        const clearBtn = document.getElementById('filterClear');
        const noMatchRow = document.getElementById('noMatchRow');
        const paginationEl = document.getElementById('pagination');
        const tbody = document.getElementById('empTableBody');
        if (!tbody) return;

        const PAGE_SIZE = 10;
        let currentPage = 1;

        const rows = Array.from(tbody.querySelectorAll('tr[data-name]'));

        function getMatches() {
            const q = (searchInput.value || '').trim().toLowerCase();
            const cat = categoryFilter.value || '';
            const month = monthFilter.value || '';
            const wantActive = activeToggle.classList.contains('active');
            const wantInactive = inactiveToggle.classList.contains('active');

            return rows.filter(row => {
                const matchesName = !q || row.dataset.name.indexOf(q) !== -1;
                const matchesCategory = !cat || row.dataset.category === cat;
                const matchesMonth = !month || (row.dataset.dateHired && row.dataset.dateHired.substring(5, 7) === month);
                const matchesStatus = (!wantActive && !wantInactive) ||
                    (wantActive && row.dataset.status === 'active') ||
                    (wantInactive && row.dataset.status === 'inactive');
                return matchesName && matchesCategory && matchesMonth && matchesStatus;
            });
        }

        function sortMatches(matches) {
            const val = sortBy.value;
            if (val === 'tenure_desc' || val === 'tenure_asc') {
                // Longest tenure = the oldest date_hired (empty dates always sort last)
                return matches.slice().sort((a, b) => {
                    const da = a.dataset.dateHired || '';
                    const db = b.dataset.dateHired || '';
                    if (!da && !db) return 0;
                    if (!da) return 1;
                    if (!db) return -1;
                    return val === 'tenure_desc' ? da.localeCompare(db) : db.localeCompare(da);
                });
            }
            return matches; // 'name' = keep server order (already alphabetical)
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
            const matches = sortMatches(getMatches());
            const totalPages = Math.max(1, Math.ceil(matches.length / PAGE_SIZE));
            currentPage = Math.min(Math.max(currentPage, 1), totalPages);
            const visible = matches.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);
            renderPagination(totalPages);

            // Sort visually reorders the actual DOM rows so the NO column / order feels natural
            visible.forEach(row => tbody.appendChild(row));

            const visibleSet = new Set(visible);
            rows.forEach(row => {
                row.style.display = visibleSet.has(row) ? '' : 'none';
            });

            if (noMatchRow) noMatchRow.style.display = matches.length ? 'none' : '';
        }

        function applyFilters() {
            currentPage = 1;
            render();
        }

        searchInput.addEventListener('input', applyFilters);
        categoryFilter.addEventListener('change', applyFilters);
        monthFilter.addEventListener('change', applyFilters);
        sortBy.addEventListener('change', applyFilters);
        activeToggle.addEventListener('click', () => {
            activeToggle.classList.toggle('active');
            inactiveToggle.classList.remove('active');
            applyFilters();
        });
        inactiveToggle.addEventListener('click', () => {
            inactiveToggle.classList.toggle('active');
            activeToggle.classList.remove('active');
            applyFilters();
        });
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            categoryFilter.value = '';
            monthFilter.value = '';
            sortBy.value = 'name';
            activeToggle.classList.remove('active');
            inactiveToggle.classList.remove('active');
            applyFilters();
        });

        if (rows.length > 0) render();
    })();
</script>
@endpush