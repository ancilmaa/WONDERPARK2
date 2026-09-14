@extends('layouts.sidebar')

@section('title', 'Salary and Deductions')

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

        #toast {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translate(-50%, -12px);
            z-index: 9999;
            max-width: min(420px, calc(100vw - 40px));
            padding: 14px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
            text-align: center;
            box-shadow: var(--shadow-md);
            display: none;
            opacity: 0;
            transition: opacity .2s ease, transform .2s ease;
        }

        #toast.show {
            display: block;
            opacity: 1;
            transform: translate(-50%, 0);
        }

        #toast.success {
            background: var(--ink);
            color: #fff;
            border-left: 4px solid var(--pink);
        }

        #toast.error {
            background: var(--danger);
            color: #fff;
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

        .box {
            background: var(--card);
            padding: 26px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
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

        .rate-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 18px;
        }

        .rate-box {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
            min-width: 0;
            background: var(--bg);
        }

        .pos-badge {
            display: inline-block;
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 10px;
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

        .rate-box label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: block;
            margin-bottom: 8px;
        }

        .rate-input-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .rate-input-row span {
            font-family: 'Source Serif 4', serif;
            font-size: 15px;
            color: var(--ink-soft);
        }

        .rate-input-row input {
            padding: 9px 10px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Source Serif 4', serif;
            color: var(--ink);
            background: var(--card);
            width: 100%;
            min-width: 0;
        }

        .rate-note {
            font-size: 11px;
            color: var(--muted);
            margin-top: 14px;
        }

        .btn-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-divider {
            width: 1px;
            height: 36px;
            background: var(--line);
            flex-shrink: 0;
        }

        .btn-save {
            background: var(--pink-deep);
            color: #fff;
            border: none;
            padding: 12px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
            transition: background .15s ease, box-shadow .15s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-save:hover {
            background: var(--pink-dark);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        .btn-save:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            background: var(--ink);
        }

        .btn-generate {
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line-strong);
            padding: 12px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
            transition: background .15s ease, border-color .15s ease;
        }

        .btn-generate:hover {
            background: var(--bg);
            border-color: var(--pink);
        }

        .spinner {
            display: inline-block;
            width: 13px;
            height: 13px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            margin-right: 8px;
            vertical-align: -2px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
            font-variant-numeric: tabular-nums;
        }

        th {
            background: var(--ink);
            color: #fff;
            padding: 11px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid var(--line);
            font-size: 12.5px;
            color: var(--ink-soft);
            white-space: nowrap;
        }

        td strong {
            color: var(--ink);
            font-weight: 600;
        }

        tbody tr:hover:not(.category-row) {
            background: var(--pink-pale);
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

        .btn-payslip {
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line-strong);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .15s ease, border-color .15s ease;
            white-space: nowrap;
        }

        .btn-payslip:hover {
            background: var(--pink-pale);
            border-color: var(--pink);
            color: var(--pink-deep);
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

        .salary-card-name {
            font-family: 'Source Serif 4', serif;
            font-size: 14px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 10px;
        }

        .salary-card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 12px;
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

        .scg-val.is-deduct {
            color: var(--danger);
        }

        .scg-val.is-muted {
            color: var(--muted);
        }

        .scg-val.is-present {
            color: var(--present);
            font-size: 15px;
        }

        .card-category-label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 10px 0 6px;
        }

        .salary-card-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-top: 10px;
            margin-top: 10px;
            border-top: 1px dashed var(--line-strong);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 21, 35, .55);
            z-index: 200;
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
            padding: 26px;
            max-width: 560px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: var(--shadow-md);
        }

        .modal-box h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .modal-sub {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 18px;
        }

        .holiday-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .holiday-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--bg);
        }

        .holiday-row .day-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
        }

        .holiday-row select {
            padding: 7px 10px;
            border: 1px solid var(--line-strong);
            border-radius: 8px;
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            color: var(--ink-soft);
            background: #fff;
            cursor: pointer;
            min-width: 150px;
        }

        .holiday-row select.marked {
            border-color: var(--pink);
            color: var(--pink-deep);
            font-weight: 600;
            background: var(--pink-pale);
        }

        .modal-note {
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-cancel {
            background: #fff;
            color: var(--ink-soft);
            border: 1px solid var(--line-strong);
            padding: 11px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background: var(--bg);
        }

        .btn-confirm {
            background: var(--ink);
            color: #fff;
            border: none;
            padding: 11px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-confirm:hover {
            background: #000;
        }

        .btn-confirm:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .cutoff-select-wrap {
            margin-bottom: 18px;
        }

        .cutoff-select-wrap label {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            display: block;
            margin-bottom: 8px;
        }

        .cutoff-select-wrap select {
            width: 100%;
            padding: 9px 10px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--ink-soft);
            background: #fff;
            cursor: pointer;
        }

        .govt-toggle-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            background: var(--bg);
        }

        .govt-toggle-wrap input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--pink-deep);
            cursor: pointer;
            flex-shrink: 0;
        }

        .govt-toggle-wrap label {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            cursor: pointer;
            margin: 0;
        }

        @media(max-width:900px) {
            .toolbar h2 {
                font-size: 1.05rem;
            }

            .box {
                padding: 16px;
            }

            .rate-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .btn-row {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-divider {
                width: 100%;
                height: 1px;
            }

            .btn-save,
            .btn-generate {
                width: 100%;
            }

            .table-scroll {
                display: none;
            }

            .card-list {
                display: block;
            }

            .holiday-row {
                flex-direction: column;
                align-items: stretch;
            }

            .holiday-row select {
                min-width: 0;
                width: 100%;
            }
        }

        @media(max-width:520px) {
            .salary-card-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
@endsection

@section('content')

    <div class="toolbar">
        <div class="eyebrow">Lipa Branch &middot; Salary and Deductions</div>
        <h2>Salary and Deductions</h2>
    </div>

    @if (session('success'))
        <span id="flash-success" data-msg="{{ session('success') }}" style="display:none;"></span>
    @endif
    @if (session('error'))
        <span id="flash-error" data-msg="{{ session('error') }}" style="display:none;"></span>
    @endif

    <div class="box">
        <h3>Daily Rate Settings</h3>
        <form action="{{ route('salary.rates') }}" method="POST" id="ratesForm">
            @csrf
            <div class="rate-grid">
                <div class="rate-box">
                    <span class="pos-badge pos-manager">Manager</span>
                    <label>Daily Rate</label>
                    <div class="rate-input-row">
                        <span>&#8369;</span>
                        <input type="number" name="rates[manager]" value="{{ $rates->get('manager')?->daily_rate ?? 0 }}"
                            min="0" step="50" required>
                    </div>
                </div>
                <div class="rate-box">
                    <span class="pos-badge pos-tl">Team Leader</span>
                    <label>Daily Rate</label>
                    <div class="rate-input-row">
                        <span>&#8369;</span>
                        <input type="number" name="rates[team_leader]"
                            value="{{ $rates->get('team_leader')?->daily_rate ?? 0 }}" min="0" step="50"
                            required>
                    </div>
                </div>
                <div class="rate-box">
                    <span class="pos-badge pos-staff">Staff</span>
                    <label>Daily Rate</label>
                    <div class="rate-input-row">
                        <span>&#8369;</span>
                        <input type="number" name="rates[staff]" value="{{ $rates->get('staff')?->daily_rate ?? 0 }}"
                            min="0" step="50" required>
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-save" id="btnSaveRates">Save Rates</button>
                <div class="btn-divider"></div>
                <button type="button" class="btn-generate" id="openHolidayModal">
                    &#9654; Generate Salary
                </button>
            </div>
            <p class="rate-note">&#9888;&#65039; Changes apply to future payrolls only. Past records are not affected.</p>
        </form>

        <form id="generateForm" action="{{ route('salary.store') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>

    <div class="box">
        <h3>Salary Summary</h3>

        @php $grouped = $attendance->groupBy(fn($a) => $a->category ?? 'Staff'); @endphp

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
                        <th>Payslip</th>
                    </tr>
                </thead>
                <tbody>
                    @php $idx = 0; @endphp
                    @forelse($grouped as $category => $group)
                        <tr class="category-row">
                            <td colspan="10">{{ $category }}</td>
                        </tr>
                        @foreach ($group as $item)
                            @php
                                $sal = $salaryMap->get($item->name);
                                $daysWorked = 0;
                                for ($i = 1; $i <= 15; $i++) {
                                    if (($item->{"day_{$i}"} ?? '') === 'P') {
                                        $daysWorked++;
                                    }
                                }
                                $totalDeductions = $sal
                                    ? $sal->deduction +
                                        $sal->sss_deduction +
                                        $sal->philhealth_deduction +
                                        $sal->pagibig_deduction +
                                        $sal->withholding_tax
                                    : null;
                            @endphp
                            <tr data-idx="{{ $idx++ }}">
                                <td><strong>{{ $item->name }}</strong></td>
                                <td>{{ $daysWorked }}</td>
                                <td>{{ $sal ? '₱' . number_format($sal->basic_salary, 2) : '-' }}</td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->sss_deduction, 2) : '-' }}
                                </td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->philhealth_deduction, 2) : '-' }}
                                </td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->pagibig_deduction, 2) : '-' }}
                                </td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->withholding_tax, 2) : '-' }}
                                </td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">
                                    {{ $sal ? '₱' . number_format($totalDeductions, 2) : '-' }}
                                </td>
                                <td class="{{ $sal ? 'money' : 'muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->net_salary, 2) : '-' }}
                                </td>
                                <td>
                                    @if ($sal)
                                        <a href="{{ route('payslip.show', $sal->id) }}" class="btn-payslip">
                                            <i class="fa-regular fa-file-lines"></i>
                                            View
                                        </a>
                                    @else
                                        <span class="muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;padding:24px;color:var(--muted);">
                                No records yet. Please import a Bio file first.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-list">
            @php $cidx = 0; @endphp
            @forelse($grouped as $category => $group)
                <div class="card-category-label">{{ $category }}</div>
                @foreach ($group as $item)
                    @php
                        $sal = $salaryMap->get($item->name);
                        $daysWorked = 0;
                        for ($i = 1; $i <= 15; $i++) {
                            if (($item->{"day_{$i}"} ?? '') === 'P') {
                                $daysWorked++;
                            }
                        }
                        $totalDeductions = $sal
                            ? $sal->deduction +
                                $sal->sss_deduction +
                                $sal->philhealth_deduction +
                                $sal->pagibig_deduction +
                                $sal->withholding_tax
                            : null;
                    @endphp
                    <div class="salary-card" data-idx="{{ $cidx++ }}">
                        <div class="salary-card-name">{{ $item->name }}</div>
                        <div class="salary-card-grid">
                            <div>
                                <div class="scg-label">Days worked</div>
                                <div class="scg-val">{{ $daysWorked }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Basic pay</div>
                                <div class="scg-val">{{ $sal ? '₱' . number_format($sal->basic_salary, 2) : '-' }}</div>
                            </div>
                            <div>
                                <div class="scg-label">SSS</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->sss_deduction, 2) : '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="scg-label">PhilHealth</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->philhealth_deduction, 2) : '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="scg-label">Pag-IBIG</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->pagibig_deduction, 2) : '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="scg-label">Withholding tax</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->withholding_tax, 2) : '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="scg-label">Deductions</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">
                                    {{ $sal ? '₱' . number_format($totalDeductions, 2) : '-' }}
                                </div>
                            </div>
                            <div style="grid-column:1 / -1;">
                                <div class="scg-label">Net salary</div>
                                <div class="scg-val {{ $sal ? 'is-present' : 'is-muted' }}">
                                    {{ $sal ? '₱' . number_format($sal->net_salary, 2) : '-' }}
                                </div>
                            </div>
                        </div>
                        @if ($sal)
                            <div class="salary-card-footer">
                                <a href="{{ route('payslip.show', $sal->id) }}" class="btn-payslip">
                                    <i class="fa-regular fa-file-lines"></i>
                                    View payslip
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            @empty
                <p style="text-align:center;padding:20px;color:var(--muted);font-size:13px;">
                    No records yet. Please import a Bio file first.
                </p>
            @endforelse
        </div>

        <div class="pagination" id="pagination"></div>

    </div>

    <div class="modal-overlay" id="holidayModalOverlay">
        <div class="modal-box">
            <h3>Mark Holidays Before Generating</h3>
            <p class="modal-sub">Select which day(s) in this cutoff (Day 1&ndash;15) fall on a holiday, if any.</p>

            <div class="cutoff-select-wrap">
                <label>Cutoff Type</label>
                <select id="cutoffTypeSelect">
                    <option value="1st">1st Cutoff (Day 1&ndash;15)</option>
                    <option value="2nd">2nd Cutoff (Day 16&ndash;31)</option>
                </select>
            </div>

            <div class="govt-toggle-wrap">
                <input type="checkbox" id="applyGovtDeductions">
                <label for="applyGovtDeductions">Apply SSS / PhilHealth / Pag-IBIG deductions this cutoff</label>
            </div>

            <div class="holiday-list" id="holidayList">
            </div>

            <p class="modal-note">
                &#8226; <strong>Regular Holiday</strong> pays 200% of the daily rate for employees present that day.<br>
                &#8226; <strong>Special Non-Working</strong> pays 130% of the daily rate for employees present that day.<br>
                &#8226; Leave as "Not a holiday" for ordinary working days.<br>
                &#8226; Ang SSS/PhilHealth/Pag-IBIG ay ide-deduct lang kapag naka-check ang checkbox sa itaas &mdash; hindi
                na ito automatic base sa cutoff.
            </p>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="cancelHolidayModal">Cancel</button>
                <button type="button" class="btn-confirm" id="confirmGenerate">Generate Salary</button>
            </div>
        </div>
    </div>

    <div id="toast"></div>

@endsection

@push('scripts')
    <script>
        (function() {
            const toast = document.getElementById('toast');

            function showToast(msg, type) {
                toast.innerText = msg;
                toast.className = type;
                toast.style.display = 'block';
                requestAnimationFrame(() => toast.classList.add('show'));
                clearTimeout(toast._hideTimer);
                toast._hideTimer = setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 200);
                }, 5000);
            }

            const successMsg = document.getElementById('flash-success')?.dataset.msg;
            const errorMsg = document.getElementById('flash-error')?.dataset.msg;

            if (successMsg) showToast(successMsg, 'success');
            if (errorMsg) showToast(errorMsg, 'error');
        })();

        (function() {
            const form = document.getElementById('ratesForm');
            const btn = document.getElementById('btnSaveRates');
            if (!form || !btn) return;

            form.addEventListener('submit', function() {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span>Saving rates...';
            });
        })();

        (function() {
            const openBtn = document.getElementById('openHolidayModal');
            const cancelBtn = document.getElementById('cancelHolidayModal');
            const confirmBtn = document.getElementById('confirmGenerate');
            const overlayEl = document.getElementById('holidayModalOverlay');
            const listEl = document.getElementById('holidayList');
            const generateForm = document.getElementById('generateForm');
            const cutoffSelect = document.getElementById('cutoffTypeSelect');
            const govtCheckbox = document.getElementById('applyGovtDeductions');
            if (!openBtn || !generateForm) return;

            for (let d = 1; d <= 15; d++) {
                const row = document.createElement('div');
                row.className = 'holiday-row';
                row.innerHTML = `
            <span class="day-label">Day ${d}</span>
            <select data-day="${d}">
                <option value="">Not a holiday</option>
                <option value="regular">Regular Holiday (200%)</option>
                <option value="special">Special Non-Working (130%)</option>
            </select>
        `;
                listEl.appendChild(row);
            }

            listEl.querySelectorAll('select').forEach(sel => {
                sel.addEventListener('change', () => {
                    sel.classList.toggle('marked', sel.value !== '');
                });
            });

            openBtn.addEventListener('click', () => overlayEl.classList.add('active'));
            cancelBtn.addEventListener('click', () => overlayEl.classList.remove('active'));
            overlayEl.addEventListener('click', (e) => {
                if (e.target === overlayEl) overlayEl.classList.remove('active');
            });

            confirmBtn.addEventListener('click', () => {
                generateForm.querySelectorAll(
                    'input[name^="holidays"], input[name="cutoff_type"], input[name="apply_govt_deductions"]'
                    ).forEach(el => el.remove());

                const cutoffInput = document.createElement('input');
                cutoffInput.type = 'hidden';
                cutoffInput.name = 'cutoff_type';
                cutoffInput.value = cutoffSelect.value;
                generateForm.appendChild(cutoffInput);

                const govtInput = document.createElement('input');
                govtInput.type = 'hidden';
                govtInput.name = 'apply_govt_deductions';
                govtInput.value = govtCheckbox.checked ? '1' : '0';
                generateForm.appendChild(govtInput);

                listEl.querySelectorAll('select').forEach(sel => {
                    if (sel.value) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `holidays[${sel.dataset.day}]`;
                        input.value = sel.value;
                        generateForm.appendChild(input);
                    }
                });

                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner"></span>Generating...';

                generateForm.submit();
            });
        })();

        (function() {
            const paginationEl = document.getElementById('pagination');
            const tbody = document.querySelector('.table-scroll table tbody');
            const cardList = document.querySelector('.card-list');
            if (!tbody) return;

            const PAGE_SIZE = 10;
            let currentPage = 1;

            function groupRows(container, catSelector, itemSelector) {
                const groups = [];
                let current = null;
                Array.from(container.children).forEach(el => {
                    if (el.matches(catSelector)) {
                        current = {
                            catEl: el,
                            items: []
                        };
                        groups.push(current);
                    } else if (el.matches(itemSelector) && current) {
                        current.items.push(el);
                    }
                });
                return groups;
            }

            const tableGroups = groupRows(tbody, '.category-row', 'tr[data-idx]');
            const cardGroups = cardList ? groupRows(cardList, '.card-category-label', '.salary-card[data-idx]') : [];
            const total = tableGroups.reduce((n, g) => n + g.items.length, 0);

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

            function applyToGroups(groups, start, end) {
                groups.forEach(g => {
                    let groupVisible = false;
                    g.items.forEach(el => {
                        const idx = parseInt(el.dataset.idx, 10);
                        const show = idx >= start && idx < end;
                        el.style.display = show ? '' : 'none';
                        if (show) groupVisible = true;
                    });
                    g.catEl.style.display = groupVisible ? '' : 'none';
                });
            }

            function render() {
                const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);
                const start = (currentPage - 1) * PAGE_SIZE;
                const end = start + PAGE_SIZE;

                applyToGroups(tableGroups, start, end);
                applyToGroups(cardGroups, start, end);
                renderPagination(totalPages);
            }

            if (total > 0) render();
        })();
    </script>
@endpush
