@extends('layouts.sidebar')

@section('title', 'Payroll History')

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
    <style>
        :root {
            --danger: #B82850;
            --danger-soft: #FFE3EB;
        }

        .serif {
            font-family: 'Source Serif 4', serif;
        }

        .toolbar {
            background: var(--card);
            padding: 22px 26px;
            margin-bottom: 20px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
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

        .alert-box {
            border-radius: 12px;
            padding: 12px 18px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success {
            background: var(--present-soft);
            color: var(--present);
        }

        .alert-error {
            background: var(--deduct-soft);
            color: var(--danger);
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
            min-width: 0;
        }

        .stat-label {
            font-size: 10.5px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }

        .stat-value {
            font-family: 'Source Serif 4', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--ink);
            margin-top: 6px;
            word-break: break-word;
        }

        .stat-value.green {
            color: var(--present);
        }

        .stat-value.red {
            color: var(--danger);
        }

        .stat-value.pink {
            color: var(--pink-deep);
        }

        .box {
            background: var(--card);
            padding: 26px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .box h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
        }

        .filters {
            display: flex;
            gap: 10px;
            align-items: center;
            padding-bottom: 20px;
            flex-wrap: wrap;
        }

        .filters label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: block;
            margin-bottom: 6px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        select,
        input[type=text] {
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--ink-soft);
            background: var(--bg);
            outline: none;
        }

        select:focus,
        input[type=text]:focus {
            border-color: var(--pink);
            background: #fff;
        }

        .btn-search {
            background: var(--pink-deep);
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background .15s ease, box-shadow .15s ease;
            align-self: flex-end;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-search:hover {
            background: var(--pink-dark);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-danger:hover {
            background: #96143E;
            box-shadow: 0 0 0 3px var(--danger-soft);
        }

        .divider-v {
            width: 1px;
            height: 36px;
            background: var(--line);
            align-self: flex-end;
            margin-bottom: 2px;
        }

        .filter-clear-btn {
            background: none;
            border: none;
            color: var(--pink-deep);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            font-family: 'Inter', sans-serif;
            padding: 9px 4px;
            align-self: flex-end;
            margin-bottom: 2px;
        }

        .pagination {
            display: none;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 6px;
            padding-top: 18px;
            margin-top: 6px;
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

        .batch-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--pink-pale);
            color: var(--pink-deep);
            font-weight: 700;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 999px;
            margin-left: 10px;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-variant-numeric: tabular-nums;
            table-layout: auto;
        }

        thead th {
            background: var(--ink);
            color: #fff;
            padding: 11px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid var(--line);
            transition: background .1s;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover:not(.category-row) {
            background: var(--pink-pale);
        }

        tbody td {
            padding: 12px;
            font-size: 12.5px;
            color: var(--ink-soft);
            vertical-align: middle;
            white-space: nowrap;
        }

        .td-name {
            font-weight: 600;
            color: var(--ink);
        }

        .money {
            color: var(--present);
            font-weight: 700;
        }

        .deduction {
            color: var(--danger);
            font-weight: 600;
        }

        .muted {
            color: var(--muted);
        }

        .category-row td {
            background: var(--pink-light);
            color: var(--pink-deep);
            font-weight: 700;
            font-size: 10.5px;
            padding: 8px 14px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            border-top: 1px solid var(--line-strong);
            border-bottom: 1px solid var(--line-strong);
        }

        .pos-badge {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            white-space: nowrap;
        }

        .pos-manager {
            background: var(--ink);
            color: #fff;
        }

        .pos-tl {
            background: var(--pink-light);
            color: var(--pink-deep);
        }

        .pos-staff {
            background: var(--card);
            color: var(--ink-soft);
            border: 1px solid var(--line-strong);
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 18px;
            margin-top: 6px;
            border-top: 1px solid var(--line);
            flex-wrap: wrap;
            gap: 8px;
        }

        .table-footer-info {
            font-size: 11.5px;
            color: var(--muted);
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: var(--muted);
            font-size: 13px;
        }

        .empty-state svg {
            margin-bottom: 12px;
            opacity: 0.4;
        }

        .card-list {
            display: none;
        }

        .salary-card {
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 10px;
        }

        .salary-card:last-child {
            margin-bottom: 0;
        }

        .salary-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            gap: 8px;
        }

        .salary-card-name {
            font-family: 'Source Serif 4', serif;
            font-size: 14px;
            font-weight: 700;
            color: var(--ink);
        }

        .salary-card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
            margin-bottom: 10px;
        }

        .scg-label {
            font-size: 9.5px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .scg-val {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            margin-top: 2px;
        }

        .salary-card-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-top: 10px;
            border-top: 1px dashed var(--line-strong);
        }

        .net-tag {
            font-family: 'Source Serif 4', serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--present);
        }

        .card-category-label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 10px 0 6px;
        }

        /* ---- Delete confirmation modal ---- */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 21, 35, .55);
            z-index: 300;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: var(--card);
            border-radius: 16px;
            padding: 28px;
            max-width: 420px;
            width: 100%;
            box-shadow: var(--shadow-md);
            text-align: center;
        }

        .delete-modal-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--danger-soft);
            color: var(--danger);
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }

        .modal-box h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .modal-sub {
            font-size: 13px;
            color: var(--ink-soft);
            line-height: 1.5;
            margin-bottom: 22px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-cancel {
            background: #fff;
            color: var(--ink-soft);
            border: 1px solid var(--line-strong);
            padding: 11px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
            transition: background .15s ease;
        }

        .btn-cancel:hover {
            background: var(--bg);
        }

        .btn-confirm-danger {
            background: var(--danger);
            color: #fff;
            border: none;
            padding: 11px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .15s ease, box-shadow .15s ease;
        }

        .btn-confirm-danger:hover {
            background: #96143E;
            box-shadow: 0 0 0 3px var(--danger-soft);
        }

        /* ---- Loading overlay ---- */
        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 21, 35, .55);
            backdrop-filter: blur(2px);
            z-index: 250;
            align-items: center;
            justify-content: center;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-box {
            background: var(--card);
            border-radius: 20px;
            padding: 38px 46px;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            min-width: 280px;
        }

        .loading-icon-wrap {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--pink-pale);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            position: relative;
        }

        .loading-icon-wrap i {
            font-size: 22px;
            color: var(--pink-deep);
        }

        .loading-ring {
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 3px solid var(--pink-light);
            border-top-color: var(--pink-deep);
            animation: spin .9s linear infinite;
        }

        .loading-title {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
            text-transform: uppercase;
            letter-spacing: 1.6px;
            margin-bottom: 6px;
        }

        .loading-sub {
            font-size: 12px;
            color: var(--muted);
            text-align: center;
            margin-bottom: 16px;
        }

        .loading-dots {
            display: flex;
            gap: 5px;
        }

        .loading-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--pink);
            animation: dotPulse 1.2s ease-in-out infinite;
        }

        .loading-dots span:nth-child(2) {
            animation-delay: .15s;
        }

        .loading-dots span:nth-child(3) {
            animation-delay: .3s;
        }

        @keyframes dotPulse {

            0%,
            80%,
            100% {
                opacity: .25;
                transform: scale(.8);
            }

            40% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .spinner-lg {
            width: 30px;
            height: 30px;
            border: 3px solid var(--line-strong);
            border-top-color: var(--pink-deep);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width:1024px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:900px) {
            .toolbar h2 {
                font-size: 1.05rem;
            }

            .box {
                padding: 16px;
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-card {
                padding: 14px 16px;
            }

            .stat-value {
                font-size: 18px;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group select,
            .filter-group input[type=text] {
                width: 100%;
            }

            .divider-v {
                display: none;
            }

            .btn-search {
                width: 100%;
                padding: 11px;
                align-self: stretch;
                justify-content: center;
            }

            .table-scroll {
                display: none;
            }

            .card-list {
                display: block;
            }

            .table-footer {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width:520px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')

    <div class="toolbar">
        <div class="eyebrow">Lipa Branch &middot; Payroll and Payslip</div>
        <h2>Payroll History</h2>
    </div>

    @if (session('error'))
        <div class="alert-box alert-error">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total employees</div>
            <div class="stat-value pink">{{ $totalEmployees }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total basic pay</div>
            <div class="stat-value">&#8369;{{ number_format($totalBasic, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total deductions</div>
            <div class="stat-value red">&#8369;{{ number_format($totalDeductions, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total net salary</div>
            <div class="stat-value green">&#8369;{{ number_format($totalNet, 2) }}</div>
        </div>
    </div>

    <div class="box">
        <h3>
            Filter Records
            @if ($selectedBatch && $isLatestBatch)
                <span class="batch-tag"><i class="fa-solid fa-star"></i> Latest Batch</span>
            @endif
        </h3>

        <form method="GET" action="{{ route('payroll-history') }}" id="filterForm">
            <div class="filters">
                <div class="filter-group" style="min-width:280px;">
                    <label>Payroll Period</label>
                    <select name="batch" id="batchSelect">
                        @forelse($batches as $b)
                            <option value="{{ $b->batch_id }}" {{ $selectedBatch === $b->batch_id ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($b->pay_date)->format('M j, Y') }}
                                &mdash; {{ $b->cutoff_type }} Cutoff
                                &middot; Generated {{ \Carbon\Carbon::parse($b->generated_at)->format('M j, Y g:i A') }}
                                {{ $b->batch_id === ($batches->first()->batch_id ?? null) ? '(Latest)' : '' }}
                            </option>
                        @empty
                            <option value="">No payroll batches yet</option>
                        @endforelse
                    </select>
                </div>

                <div class="divider-v"></div>

                <div class="filter-group">
                    <label>Category</label>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        @foreach ($grouped ?? $salaries->groupBy('category') as $cat => $items)
                            <option value="{{ strtolower($cat ?: 'uncategorized') }}">{{ $cat ?: 'Uncategorized' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Employee</label>
                    <input type="text" id="employeeSearch" placeholder="Search employee&hellip;" style="width:190px;"
                        autocomplete="off">
                </div>
                <button type="button" class="filter-clear-btn" id="filterClear">Clear filters</button>

                @if ($selectedBatch)
                    <button type="button" class="btn-search btn-danger" style="margin-left:auto;"
                        onclick="confirmDeleteBatch()">
                        <i class="fa-regular fa-trash-can"></i> Delete This Batch
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="box">
        <h3>Payroll Records</h3>

        @php $grouped = $salaries->groupBy('category'); @endphp

        {{-- Desktop / tablet table --}}
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Days Worked</th>
                        <th>Basic Pay</th>
                        <th>SSS</th>
                        <th>PhilHealth</th>
                        <th>Pag-IBIG</th>
                        <th>W/Tax</th>
                        <th>Deductions</th>
                        <th>Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grouped as $category => $group)
                        <tr class="category-row" data-cat-row="{{ strtolower($category ?: 'uncategorized') }}">
                            <td colspan="9">{{ $category ?: 'Uncategorized' }}</td>
                        </tr>
                        @foreach ($group as $sal)
                            @php
                                $totalDeductions =
                                    $sal->deduction +
                                    $sal->sss_deduction +
                                    $sal->philhealth_deduction +
                                    $sal->pagibig_deduction +
                                    $sal->withholding_tax;
                            @endphp
                            <tr data-name="{{ strtolower($sal->name) }}"
                                data-category="{{ strtolower($sal->category ?: 'uncategorized') }}">
                                <td class="td-name">{{ $sal->name }}</td>
                                <td>{{ $sal->present_days ?? ($sal->days_worked ?? '-') }}</td>
                                <td>&#8369;{{ number_format($sal->basic_salary, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($sal->sss_deduction, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($sal->philhealth_deduction, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($sal->pagibig_deduction, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($sal->withholding_tax, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($totalDeductions, 2) }}</td>
                                <td class="money">&#8369;{{ number_format($sal->net_salary, 2) }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <p>No payroll records found.</p>
                                    <p style="margin-top:4px;">Generate salary first from the Salary &amp; Deductions page.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noMatchRow" style="display:none;">
                        <td colspan="9" style="text-align:center;padding:24px;color:var(--muted);">No employees match
                            your search or filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile card list --}}
        <div class="card-list">
            @forelse($grouped as $category => $group)
                <div class="card-category-label" data-cat-row="{{ strtolower($category ?: 'uncategorized') }}">
                    {{ $category ?: 'Uncategorized' }}</div>
                @foreach ($group as $sal)
                    @php
                        $totalDeductions =
                            $sal->deduction +
                            $sal->sss_deduction +
                            $sal->philhealth_deduction +
                            $sal->pagibig_deduction +
                            $sal->withholding_tax;
                    @endphp
                    <div class="salary-card" data-name="{{ strtolower($sal->name) }}"
                        data-category="{{ strtolower($sal->category ?: 'uncategorized') }}">
                        <div class="salary-card-top">
                            <span class="salary-card-name">{{ $sal->name }}</span>
                            @if (strtolower($sal->category) === 'manager')
                                <span class="pos-badge pos-manager">Manager</span>
                            @elseif(str_contains(strtolower($sal->category ?? ''), 'leader'))
                                <span class="pos-badge pos-tl">Team Leader</span>
                            @else
                                <span class="pos-badge pos-staff">Staff</span>
                            @endif
                        </div>
                        <div class="salary-card-grid">
                            <div>
                                <div class="scg-label">Days worked</div>
                                <div class="scg-val">{{ $sal->present_days ?? ($sal->days_worked ?? '-') }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Basic pay</div>
                                <div class="scg-val">&#8369;{{ number_format($sal->basic_salary, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">SSS</div>
                                <div class="scg-val" style="color:var(--danger);">
                                    &#8369;{{ number_format($sal->sss_deduction, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">PhilHealth</div>
                                <div class="scg-val" style="color:var(--danger);">
                                    &#8369;{{ number_format($sal->philhealth_deduction, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Pag-IBIG</div>
                                <div class="scg-val" style="color:var(--danger);">
                                    &#8369;{{ number_format($sal->pagibig_deduction, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Withholding tax</div>
                                <div class="scg-val" style="color:var(--danger);">
                                    &#8369;{{ number_format($sal->withholding_tax, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Total deductions</div>
                                <div class="scg-val" style="color:var(--danger);">
                                    &#8369;{{ number_format($totalDeductions, 2) }}</div>
                            </div>
                        </div>
                        <div class="salary-card-footer">
                            <span class="net-tag">&#8369;{{ number_format($sal->net_salary, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="empty-state">
                    <p>No payroll records found.</p>
                    <p style="margin-top:4px;">Generate salary first from the Salary &amp; Deductions page.</p>
                </div>
            @endforelse
        </div>

        <div class="pagination" id="pagination"></div>

    </div>

    {{-- Hidden form used to submit the Delete Batch action --}}
    @if ($selectedBatch)
        <form id="deleteBatchForm" method="POST" action="{{ route('payroll-history.delete-batch', $selectedBatch) }}"
            style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endif

    {{-- Loading overlay: shown while switching Payroll Period --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-box">
            <div class="loading-icon-wrap">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span class="loading-ring"></span>
            </div>
            <div class="loading-title">Records Processing</div>
            <div class="loading-dots"><span></span><span></span><span></span></div>
        </div>
    </div>

    {{-- Custom delete confirmation modal --}}
    <div class="modal-overlay" id="deleteModalOverlay">
        <div class="modal-box">
            <div class="delete-modal-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h3>Delete this batch?</h3>
            <p class="modal-sub">This batch will be permanently deleted.</p>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="cancelDeleteModal">Cancel</button>
                <button type="button" class="btn-confirm-danger" id="confirmDeleteModal">
                    <i class="fa-regular fa-trash-can"></i> Delete Batch
                </button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function confirmDeleteBatch() {
            document.getElementById('deleteModalOverlay').classList.add('active');
        }

        (function() {
            const modalOverlay = document.getElementById('deleteModalOverlay');
            const cancelBtn = document.getElementById('cancelDeleteModal');
            const confirmBtn = document.getElementById('confirmDeleteModal');
            if (!modalOverlay) return;

            cancelBtn.addEventListener('click', () => modalOverlay.classList.remove('active'));
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) modalOverlay.classList.remove('active');
            });
            confirmBtn.addEventListener('click', () => {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML =
                    '<span class="spinner-lg" style="width:14px;height:14px;border-width:2px;margin:0;"></span> Deleting...';
                document.getElementById('deleteBatchForm').submit();
            });
        })();

        /* ---- Loading overlay: shown when the Payroll Period (batch) changes, since that's the only remaining server round-trip ---- */
        (function() {
            const batchSelect = document.getElementById('batchSelect');
            const filterForm = document.getElementById('filterForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (!batchSelect || !filterForm || !loadingOverlay) return;

            batchSelect.addEventListener('change', () => {
                loadingOverlay.classList.add('active');
                // Double requestAnimationFrame ensures the browser actually paints
                // the overlay before the page navigates away — submitting immediately
                // can sometimes beat the paint, making the overlay invisible.
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        filterForm.submit();
                    });
                });
            });

            // Fallback: also catch any other way the form might get submitted.
            filterForm.addEventListener('submit', () => {
                loadingOverlay.classList.add('active');
            });
        })();

        /* ---- Instant client-side search, category filter, and pagination (same behavior as the Attendance page) ---- */
        (function() {
            const searchInput = document.getElementById('employeeSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            const clearBtn = document.getElementById('filterClear');
            const noMatchRow = document.getElementById('noMatchRow');
            const paginationEl = document.getElementById('pagination');
            const tbody = document.querySelector('.table-scroll table tbody');
            const cardList = document.querySelector('.card-list');
            if (!searchInput || !tbody) return;

            const PAGE_SIZE = 10;
            let currentPage = 1;

            function groupRows(container, catSelector, itemSelector) {
                const groups = [];
                let current = null;
                Array.from(container.children).forEach(el => {
                    if (el.matches(catSelector)) {
                        current = {
                            catEl: el,
                            rows: []
                        };
                        groups.push(current);
                    } else if (el.matches(itemSelector) && current) {
                        current.rows.push(el);
                    }
                });
                return groups;
            }

            const tableGroups = groupRows(tbody, '.category-row', 'tr[data-name]');
            const cardGroups = cardList ? groupRows(cardList, '.card-category-label', '.salary-card[data-name]') : [];

            function getMatches() {
                const q = (searchInput.value || '').trim().toLowerCase();
                const cat = categoryFilter ? (categoryFilter.value || '') : '';
                const matches = [];
                tableGroups.forEach(g => g.rows.forEach(row => {
                    const ok = (!q || row.dataset.name.indexOf(q) !== -1) && (!cat || row.dataset
                        .category === cat);
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
                const visibleKeys = new Set(visible.map(r => r.dataset.name + '|' + r.dataset.category));

                tableGroups.forEach(g => {
                    let groupVisible = false;
                    g.rows.forEach(row => {
                        const show = visibleKeys.has(row.dataset.name + '|' + row.dataset.category);
                        row.style.display = show ? '' : 'none';
                        if (show) groupVisible = true;
                    });
                    g.catEl.style.display = groupVisible ? '' : 'none';
                });

                cardGroups.forEach(g => {
                    let groupVisible = false;
                    g.rows.forEach(row => {
                        const show = visibleKeys.has(row.dataset.name + '|' + row.dataset.category);
                        row.style.display = show ? '' : 'none';
                        if (show) groupVisible = true;
                    });
                    g.catEl.style.display = groupVisible ? '' : 'none';
                });

                if (noMatchRow) noMatchRow.style.display = matches.length ? 'none' : '';
                renderPagination(totalPages);
            }

            function applyFilters() {
                currentPage = 1;
                render();
            }

            searchInput.addEventListener('input', applyFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
            if (clearBtn) clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                if (categoryFilter) categoryFilter.value = '';
                applyFilters();
            });

            if (tableGroups.some(g => g.rows.length)) render();
        })();
    </script>
@endpush
