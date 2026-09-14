<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $sal->name }}</title>

    @php
        use Carbon\Carbon;

        $payDate = $sal->pay_date ? Carbon::parse($sal->pay_date) : Carbon::parse($sal->created_at);
        $month = $payDate->format('F');
        $year = $payDate->format('Y');

        if (($sal->cutoff_type ?? '1st') === '2nd') {
            $lastDay = $payDate->copy()->endOfMonth()->day;
            $cutoffLabel = "{$month} 16-{$lastDay}, {$year}";
        } else {
            $cutoffLabel = "{$month} 1-15, {$year}";
        }

        $presentDays = $sal->present_days ?? ($sal->days_worked ?? 0);
        $dailyRate = $presentDays > 0 ? round($sal->basic_salary / $presentDays, 2) : 0;
        $grossPay = $sal->basic_salary + ($sal->holiday_pay ?? 0);

        $totalDeduction =
            ($sal->deduction ?? 0) +
            ($sal->sss_deduction ?? 0) +
            ($sal->philhealth_deduction ?? 0) +
            ($sal->pagibig_deduction ?? 0) +
            ($sal->withholding_tax ?? 0);
    @endphp

    <style>
        :root {
            --border-color: #2E7D6E;
            --border-thin: #000;
            --ink: #1A1523;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #EFEFEF;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            padding: 30px 16px;
            font-size: 13px;
        }

        .page-wrap {
            max-width: 760px;
            margin: 0 auto;
        }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 14px;
        }

        .btn-print,
        .btn-back {
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            font-family: Arial, sans-serif;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #ccc;
            text-decoration: none;
        }

        .btn-print {
            background: #B82850;
            color: #fff;
            border: none;
        }

        .btn-print:hover {
            background: #a11f43;
        }

        .btn-back {
            background: #fff;
            color: #333;
        }

        .btn-back:hover {
            background: #f5f5f5;
        }

        .payslip-doc {
            background: #fff;
            border: 1.5px solid var(--border-thin);
        }

        .row {
            display: flex;
        }

        .cell {
            border: 1px solid var(--border-thin);
            padding: 5px 10px;
        }

        /* Header */
        .company-row {
            border: 1.5px solid var(--border-color);
        }

        .company-row .cell {
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            padding: 8px;
        }

        .dept-row {
            border-left: 1.5px solid var(--border-color);
            border-right: 1.5px solid var(--border-color);
            border-bottom: 1.5px solid var(--border-color);
        }

        .dept-row .cell {
            border: none;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            padding: 6px;
        }

        .address-block {
            text-align: center;
            padding: 10px 8px 4px;
            font-size: 12px;
            line-height: 1.5;
        }

        .payslip-title {
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 1px;
            padding: 12px 0 8px;
        }

        /* Employee info */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .info-table td {
            padding: 4px 10px;
            font-size: 12.5px;
            vertical-align: middle;
        }

        .info-label {
            font-weight: 600;
            white-space: nowrap;
            width: 150px;
        }

        .info-value {
            border-bottom: 1px solid #000;
            font-weight: 700;
            padding-bottom: 2px;
        }

        .info-value.wide {
            min-width: 220px;
        }

        /* Earnings / Deductions table */
        .pay-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }

        .pay-table th,
        .pay-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 12.5px;
        }

        .pay-table th {
            font-weight: 700;
            text-align: left;
            border-bottom: 2px solid #000;
        }

        .pay-table td.amount {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .pay-table td.label {
            text-align: left;
        }

        .pay-table .total-row td {
            font-weight: 700;
            background: #F5F5F5;
        }

        .col-amt {
            width: 120px;
        }

        .signature-block {
            padding: 26px 10px 10px;
            text-align: center;
        }

        .signature-line-wrap {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 6px;
        }

        .received-label {
            font-weight: 600;
            white-space: nowrap;
        }

        .signature-line {
            flex: 1;
            border-bottom: 1px solid #000;
            height: 26px;
        }

        .signature-caption {
            text-align: center;
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .action-bar {
                display: none;
            }

            .page-wrap {
                max-width: 100%;
            }

            .payslip-doc {
                border-width: 1.5px;
            }
        }

        @media(max-width:600px) {
            .info-table td {
                display: block;
                width: 100% !important;
                padding: 3px 10px;
            }

            .info-label {
                width: auto;
            }
        }
    </style>
</head>

<body>

    <div class="page-wrap">

        <div class="action-bar">
            <a href="{{ route('salary') }}" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            <button class="btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Payslip
            </button>
        </div>

        <div class="payslip-doc">

            <div class="row company-row">
                <div class="cell" style="flex:1;">REKS Amusement Com. Inc.</div>
            </div>
            <div class="row dept-row">
                <div class="cell" style="flex:1;">Roller Fever</div>
            </div>

            <div class="address-block">
                Lower Deck, Building J, The Outlets at Lipa<br>
                Lima Estates, Brgy. Bugtong na Pulo, Lipa City, Batangas, 4217
            </div>

            <div class="payslip-title">PAYSLIP</div>

            <table class="info-table">
                <tr>
                    <td class="info-label">Name of the Employee:</td>
                    <td colspan="3"><span class="info-value wide">{{ strtoupper($sal->name) }}</span></td>
                </tr>
                <tr>
                    <td class="info-label">Designation:</td>
                    <td colspan="3"><span class="info-value wide">{{ strtoupper($sal->category ?? '-') }}</span></td>
                </tr>
                <tr>
                    <td class="info-label">Cut Off Date:</td>
                    <td><span class="info-value">{{ $cutoffLabel }}</span></td>
                    <td class="info-label" style="text-align:right;">Salary date:</td>
                    <td><span class="info-value">{{ $payDate->format('F d, Y') }}</span></td>
                </tr>
            </table>

            <table class="pay-table">
                <thead>
                    <tr>
                        <th>Earnings</th>
                        <th class="col-amt">Amount</th>
                        <th>Deductions</th>
                        <th class="col-amt">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="label">Gross Pay</td>
                        <td class="amount">{{ number_format($grossPay, 2) }}</td>
                        <td class="label">SSS</td>
                        <td class="amount">{{ number_format($sal->sss_deduction ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total working days</td>
                        <td class="amount">{{ $presentDays }}</td>
                        <td class="label">PhilHealth</td>
                        <td class="amount">{{ number_format($sal->philhealth_deduction ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Daily Rate</td>
                        <td class="amount">{{ number_format($dailyRate, 2) }}</td>
                        <td class="label">Pag-IBIG</td>
                        <td class="amount">{{ number_format($sal->pagibig_deduction ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Holiday</td>
                        <td class="amount">{{ number_format($sal->holiday_pay ?? 0, 2) }}</td>
                        <td class="label">Withholding Tax</td>
                        <td class="amount">{{ number_format($sal->withholding_tax ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Allowances</td>
                        <td class="amount">{{ number_format($sal->allowance ?? 0, 2) }}</td>
                        <td class="label">Salary Loan</td>
                        <td class="amount">-</td>
                    </tr>
                    <tr>
                        <td class="label">Overtime</td>
                        <td class="amount">-</td>
                        <td class="label">Pag-IBIG Loan</td>
                        <td class="amount">-</td>
                    </tr>
                    <tr>
                        <td class="label">Holiday OT</td>
                        <td class="amount">-</td>
                        <td class="label">SSS Loan</td>
                        <td class="amount">-</td>
                    </tr>
                    <tr>
                        <td class="label">Refund</td>
                        <td class="amount">-</td>
                        <td class="label">Absences</td>
                        <td class="amount">{{ number_format($sal->deduction ?? 0, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label"></td>
                        <td class="amount"></td>
                        <td class="label">Total Deduction</td>
                        <td class="amount">{{ number_format($totalDeduction, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">Total Earnings &nbsp;Php</td>
                        <td class="amount">{{ number_format($grossPay, 2) }}</td>
                        <td class="label">Net Pay &nbsp;Php</td>
                        <td class="amount">{{ number_format($sal->net_salary, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="signature-block">
                <div class="signature-line-wrap">
                    <span class="received-label">Received by:</span>
                    <span class="signature-line"></span>
                </div>
                <div class="signature-caption">Signature over printed name</div>
            </div>

        </div>
    </div>

</body>

</html>
