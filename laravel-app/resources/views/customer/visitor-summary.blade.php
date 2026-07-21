@extends('layouts.sidebar')

@section('title', 'Visitor Login & Booking Summary')

@push('head')
    <link
        href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
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

        .toolbar-actions select,
        .toolbar-actions input[type="date"] {
            font-size: 12.5px;
            color: var(--ink-soft);
            font-family: 'Inter', sans-serif;
            padding: 9px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            background: var(--bg);
            cursor: pointer;
        }

        /* SUMMARY STAT CARDS */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .stat-card .icon-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #fff;
            background: var(--pink-deep);
        }

        .stat-card.card-online .icon-badge {
            background: var(--present);
        }

        .stat-card.card-age .icon-badge {
            background: var(--pink);
        }

        .stat-card.card-total .icon-badge {
            background: var(--ink);
        }

        .stat-card .label {
            font-size: .68rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 9px;
            max-width: 75%;
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

        .stat-card .sub.up {
            color: var(--present);
            font-weight: 600;
        }

        /* BOX */
        .box {
            background: var(--card);
            padding: 26px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }

        .box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }

        .box-header h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
        }

        .box-header p {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* AGE DISTRIBUTION CHART */
        .age-chart {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 4px 2px 0;
        }

        .age-row {
            display: grid;
            grid-template-columns: 64px 1fr 46px;
            align-items: center;
            gap: 14px;
        }

        .age-row .age-label-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .age-row .age-swatch {
            width: 8px;
            height: 28px;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--pink) 0%, var(--pink-deep) 100%);
            flex-shrink: 0;
        }

        .age-row.is-top .age-swatch {
            background: linear-gradient(180deg, #FF7A9C 0%, var(--pink-deep) 100%);
        }

        .age-row .age-label {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--ink);
        }

        .age-track {
            position: relative;
            height: 26px;
            border-radius: 999px;
            background: var(--pink-pale);
            overflow: hidden;
        }

        .age-fill {
            position: absolute;
            inset: 0;
            width: 0%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--pink) 0%, var(--pink-deep) 100%);
            transition: width .6s cubic-bezier(.22, 1, .36, 1);
            box-shadow: 0 2px 6px rgba(184, 40, 80, .25);
        }

        .age-row.is-top .age-fill {
            background: linear-gradient(90deg, #FF7A9C 0%, var(--pink-deep) 55%, #921F42 100%);
        }

        .age-val {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        /* TABLES (attractions + visitor logins) */
        .table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 820px;
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
            padding: 13px 12px;
            border-bottom: 1px solid var(--line);
            font-size: 12.5px;
            color: var(--ink-soft);
            vertical-align: middle;
        }

        td strong {
            color: var(--ink);
            font-weight: 600;
        }

        tbody tr:hover {
            background: var(--pink-pale);
        }

        .attraction-name {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .attraction-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: var(--pink-light);
            color: var(--pink-deep);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .age-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--pink-light);
            color: var(--pink-deep);
            font-weight: 700;
            font-size: 11.5px;
            white-space: nowrap;
        }

        .age-pill i {
            font-size: 10px;
        }

        .bookings-bar-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 150px;
        }

        .bookings-bar-track {
            flex: 1;
            height: 8px;
            border-radius: 999px;
            background: var(--line);
            overflow: hidden;
        }

        .bookings-bar-fill {
            height: 100%;
            border-radius: 999px;
            background: var(--present);
        }

        .rank-badge {
            width: 24px;
            height: 24px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            background: var(--bg);
            color: var(--ink-soft);
            flex-shrink: 0;
        }

        .rank-badge.rank-1 {
            background: var(--pink-deep);
            color: #fff;
        }

        /* VISITOR LOGINS — clickable rows that expand into a reservation panel */
        .visitor-row {
            cursor: pointer;
            transition: background .12s;
        }

        .visitor-row:hover {
            background: var(--pink-pale);
        }

        .visitor-row.is-open {
            background: var(--pink-pale);
        }

        .visitor-row.is-open td {
            border-bottom: 1px solid transparent;
        }

        .visitor-name-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .visitor-avatar {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);
            color: #fff;
            font-weight: 700;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .visitor-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--present-soft);
            color: var(--present);
            font-weight: 700;
            font-size: 11px;
        }

        .visitor-status i {
            font-size: 8px;
        }

        .expand-chevron {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            background: var(--bg);
            transition: transform .2s ease, color .15s, background .15s;
            flex-shrink: 0;
        }

        .visitor-row.is-open .expand-chevron {
            transform: rotate(180deg);
            background: var(--pink-light);
            color: var(--pink-deep);
        }

        .reservation-panel-row td {
            padding: 0;
            border-bottom: 1px solid var(--line);
        }

        .reservation-panel {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows .28s ease;
            background: var(--pink-pale);
        }

        .reservation-panel-row.is-open .reservation-panel {
            grid-template-rows: 1fr;
        }

        .reservation-panel-inner {
            overflow: hidden;
        }

        .reservation-panel-content {
            padding: 18px 22px 22px 58px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .reservation-loading {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12.5px;
            color: var(--ink-soft);
            padding: 6px 0;
        }

        .reservation-loading .spinner {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 2px solid var(--pink-light);
            border-top-color: var(--pink-deep);
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .reservation-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 26px;
        }

        .reservation-field {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 120px;
        }

        .reservation-field .k {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .reservation-field .v {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
        }

        .reservation-link-btn {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 10px;
            background: var(--ink);
            color: #fff;
            font-weight: 600;
            font-size: 12.5px;
            margin-top: 4px;
        }

        .reservation-link-btn:hover {
            background: var(--pink-deep);
        }

        .reservation-error {
            font-size: 12.5px;
            color: var(--deduct);
            padding: 6px 0;
        }

        @media(max-width:900px) {
            .stats-row {
                grid-template-columns: 1fr 1fr;
            }

            .toolbar h2 {
                font-size: 1.05rem;
            }

            .box {
                padding: 16px;
            }
        }

        @media(max-width:520px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .age-chart {
                height: 150px;
            }
        }
        /* SERVICE DISTRIBUTION SPLIT */
        .service-split {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 32px;
            align-items: start;
        }

        .service-pie-wrap {
    position: relative;
    width: 200px;
    height: 200px;
}

.service-pie-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg); /* start segments at 12 o'clock */
}

.service-pie-track {
    fill: none;
    stroke: var(--line, #E5E7EB);
    stroke-width: 34;
}

.service-pie-segment {
    fill: none;
    stroke-width: 34;
    stroke-linecap: butt;
    cursor: pointer;
    transition: filter 0.15s ease;
}

.service-pie-segment:hover {
    filter: brightness(1.1);
}

.service-pie-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    pointer-events: none;
}

.service-pie-tooltip {
    position: absolute;
    transform: translate(-50%, -100%);
    background: #1F2430;
    color: #fff;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 12px;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.12s ease;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    z-index: 20;
}

.service-pie-tooltip.is-visible {
    opacity: 1;
    visibility: visible;
}

.service-pie-tooltip-name {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    margin-bottom: 2px;
}

.service-pie-tooltip-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.service-pie-tooltip-value {
    color: rgba(255,255,255,0.8);
    font-size: 11px;
}

        .service-legend {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .service-legend-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
        }

        .service-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .service-legend-name {
            color: var(--ink);
            font-weight: 600;
            flex: 1;
        }

        .service-legend-pct {
            font-weight: 700;
            color: var(--ink);
        }

        .service-legend-count {
            color: var(--muted);
            font-size: 11px;
        }

        .service-rank-title {
            font-family: 'Source Serif 4', serif;
            font-size: 14px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 14px;
        }

        .rank-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--line);
        }

        .rank-item:last-child {
            border-bottom: none;
        }

        .rank-item-num {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: var(--bg);
            color: var(--ink-soft);
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .rank-item-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .rank-item-body {
            flex: 1;
            min-width: 0;
        }

        .rank-item-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .rank-item-service {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
        }

        .rank-item-count {
            font-family: 'Source Serif 4', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ink);
            flex-shrink: 0;
        }

        @media(max-width:800px) {
            .service-split {
                grid-template-columns: 1fr;
            }
        }
        /* AI RECOMMENDATIONS */
        .ai-box {
            background: linear-gradient(135deg, var(--card) 0%, var(--pink-pale) 100%);
            border: 1px solid var(--pink-light);
        }

        .ai-rec-card {
            display: flex;
            gap: 14px;
            padding: 16px;
            border-radius: 12px;
            background: var(--card);
            margin-bottom: 12px;
            box-shadow: var(--shadow-sm);
        }

        .ai-rec-card:last-child {
            margin-bottom: 0;
        }

        .ai-rec-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .ai-rec-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--pink-deep);
            background: var(--pink-light);
            padding: 3px 10px;
            border-radius: 999px;
            margin-bottom: 6px;
        }

        .ai-rec-body h4 {
            font-family: 'Source Serif 4', serif;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .ai-rec-desc {
            font-size: 12px;
            color: var(--ink-soft);
            line-height: 1.55;
        }
        /* LIVE TRAFFIC CHART */
        .live-chart-box .box-header {
            align-items: flex-start;
        }

        .badge-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--present-soft);
            color: var(--present);
            font-weight: 700;
            font-size: 11px;
            flex-shrink: 0;
        }

        .badge-live i {
            font-size: 7px;
        }

        .live-chart-current {
            display: flex;
            align-items: baseline;
            gap: 8px;
            margin-bottom: 16px;
        }

        .live-chart-current .num {
            font-family: 'Source Serif 4', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1;
        }

        .live-chart-current .label {
            font-size: 12px;
            color: var(--muted);
        }

        .live-chart-svg-wrap {
            position: relative;
            width: 100%;
            height: 160px;
        }

        .live-chart-svg-wrap svg {
            width: 100%;
            height: 100%;
        }

        .live-chart-empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 12.5px;
        }
    </style>
@endsection

@section('content')

    <div class="toolbar">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Visitors</div>
            <h2>Visitor Login & Booking Summary</h2>
        </div>
        <div class="toolbar-actions">
            <select id="periodFilter">
                <option value="7" {{ ($days ?? 30) == 7 ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ ($days ?? 30) == 30 ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ ($days ?? 30) == 90 ? 'selected' : '' }}>Last 3 months</option>
                <option value="365" {{ ($days ?? 30) == 365 ? 'selected' : '' }}>This year</option>
            </select>
        </div>
    </div>

    <!-- SUMMARY -->
    <div class="stats-row">
        <div class="stat-card card-online">
            <div class="icon-badge"><i class="fa-solid fa-mobile-screen-button"></i></div>
            <div class="label">Online Bookings</div>
            <div class="value">{{ $totalOnlineBookings ?? 0 }}</div>
            <div class="sub">Booked via website / app</div>
        </div>
        <div class="stat-card card-age">
            <div class="icon-badge"><i class="fa-solid fa-cake-candles"></i></div>
            <div class="label">Most Active Age Group</div>
            <div class="value">{{ $topOverallAgeGroup ?? '—' }}</div>
            <div class="sub">Across all attractions</div>
        </div>
        <div class="stat-card card-total">
            <div class="icon-badge"><i class="fa-solid fa-users"></i></div>
            <div class="label">Total Visitors</div>
            <div class="value">{{ $totalVisitors ?? 0 }}</div>
            <div class="sub">Unique visitors this period</div>
        </div>
        <div class="stat-card card-online">
    <div class="icon-badge"><i class="fa-solid fa-signal"></i></div>
    <div class="label">Live Now</div>
    <div class="value" id="liveNowValue">—</div>
    <div class="sub">Active in the last 5 mins</div>
</div>
<div class="stat-card">
    <div class="icon-badge"><i class="fa-solid fa-user-plus"></i></div>
    <div class="label">New Accounts</div>
    <div class="value">{{ $totalNewAccounts ?? 0 }}</div>
    <div class="sub">Registered this period</div>
</div>
        <div class="stat-card">
            <div class="icon-badge"><i class="fa-solid fa-trophy"></i></div>
            <div class="label">Top Attraction</div>
            <div class="value" style="font-size:1.25rem;">{{ $topAttraction ?? '—' }}</div>
            <div class="sub">Most booked this period</div>
        </div>
    </div>


  <!-- SERVICE DISTRIBUTION + TOP PACKAGES -->
    <div class="box">
        <div class="box-header">
            <div>
                <h3>Per-Service Booking Breakdown</h3>
                <p>Share of bookings per service, and the most-availed packages overall</p>
            </div>
        </div>

        <div class="service-split">
            <!-- LEFT: pie chart -->
            <div class="service-pie-col">
    <div class="service-pie-wrap">
        <svg viewBox="0 0 200 200" class="service-pie-svg">
            <circle cx="100" cy="100" r="80" class="service-pie-track" style="stroke: var(--line, #E5E7EB);" />
            @php $cumulative = 0; @endphp
            @foreach(($serviceDistribution ?? []) as $s)
                @php
                    $circumference = 2 * M_PI * 80;
                    $fraction = ($totalOnlineBookings ?? 0) > 0 ? ($s['count'] / $totalOnlineBookings) : 0;
                    $segmentLength = $fraction * $circumference;
                    $offset = $circumference - ($cumulative * $circumference);
                    $cumulative += $fraction;
                @endphp
               <circle
    cx="100" cy="100" r="80"
    class="service-pie-segment"
    style="stroke: {{ $s['color'] }};"
    stroke-dasharray="{{ round($segmentLength, 2) }} {{ round($circumference - $segmentLength, 2) }}"
    stroke-dashoffset="{{ round($offset, 2) }}"
    data-name="{{ $s['name'] }}"
    data-percentage="{{ $s['percentage'] }}"
    data-count="{{ $s['count'] }}"
    data-color="{{ $s['color'] }}"
></circle>
            @endforeach
        </svg>

        <div class="service-pie-center">
            <span class="service-pie-total">{{ $totalOnlineBookings ?? 0 }}</span>
            <span class="service-pie-total-label">Total Bookings</span>
        </div>

        <div class="service-pie-tooltip" id="servicePieTooltip">
            <div class="service-pie-tooltip-name">
                <span class="service-pie-tooltip-dot" id="tooltipDot"></span>
                <span id="tooltipName"></span>
            </div>
            <div class="service-pie-tooltip-value" id="tooltipValue"></div>
        </div>
    </div>

    <div class="service-legend">
        @forelse(($serviceDistribution ?? []) as $s)
            <div class="service-legend-row">
                <span class="service-legend-dot" style="background:{{ $s['color'] }};"></span>
                <span class="service-legend-name">{{ $s['name'] }}</span>
                <span class="service-legend-pct">{{ $s['percentage'] }}%</span>
                <span class="service-legend-count">({{ $s['count'] }})</span>
            </div>
        @empty
            <p style="color:var(--muted);font-size:12px;">No bookings yet this period.</p>
        @endforelse
    </div>
</div>
            <!-- RIGHT: ranked top packages -->
            <div class="service-rank-col">
                <h4 class="service-rank-title">Most Availed Packages</h4>

                @forelse(($packageRanking ?? []) as $pkg)
                    <div class="rank-item">
                        <span class="rank-item-num">{{ $loop->iteration }}</span>
                        <span class="rank-item-icon" style="background: {{ $pkg['color'] }};">
                            <i class="fa-solid {{ $pkg['icon'] }}"></i>
                        </span>
                        <div class="rank-item-body">
                            <div class="rank-item-name">{{ $pkg['package'] }}</div>
                            <span class="rank-item-service" style="background: {{ $pkg['color'] }}22; color: {{ $pkg['color'] }};">
                                {{ $pkg['service'] }}
                            </span>
                        </div>
                        <span class="rank-item-count">{{ $pkg['count'] }}</span>
                    </div>
                @empty
                    <p style="color:var(--muted);font-size:12.5px;">No package data yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- AI PROMO RECOMMENDATIONS -->
    <div class="box ai-box">
        <div class="box-header">
            <div>
                <h3><i class="fa-solid fa-wand-magic-sparkles" style="color:var(--pink-deep);margin-right:6px;"></i> Promo Recommendations</h3>
                <p>Suggested promos based on each attraction's top-booking age group</p>
            </div>
        </div>

        @forelse(($recommendations ?? []) as $rec)
            <div class="ai-rec-card">
                <span class="ai-rec-icon" style="background: {{ $rec['color'] }};">
                    <i class="fa-solid {{ $rec['icon'] }}"></i>
                </span>
                <div class="ai-rec-body">
                    <span class="ai-rec-tag"><i class="fa-solid fa-cake-candles"></i> {{ $rec['attraction'] }} · {{ $rec['age_group'] }}</span>
                    <h4>{{ $rec['title'] }}</h4>
                    <p class="ai-rec-desc">{{ $rec['desc'] }}</p>
                </div>
            </div>
        @empty
            <p style="text-align:center;padding:24px;color:var(--muted);">No recommendations yet — need more booking data first.</p>
        @endforelse
    </div>
   <!-- LIVE SITE TRAFFIC (real-time) -->
    <div class="box live-chart-box">
        <div class="box-header">
            <div>
                <h3>Live Site Traffic</h3>
                <p>Visitors actively on the site, updated every 15 seconds</p>
            </div>
            <span class="badge-live"><i class="fa-solid fa-circle"></i> Live</span>
        </div>

        <div class="live-chart-current">
            <span class="num" id="liveChartCurrentNum">—</span>
            <span class="label">visitors online right now</span>
        </div>

        <div class="live-chart-svg-wrap">
            <svg id="liveChartSvg" preserveAspectRatio="none"></svg>
            <p class="live-chart-empty" id="liveChartEmpty">Gathering live data…</p>
        </div>
    </div>

    <div class="box">
    <div class="box-header">
        <div><h3>New Accounts by Age Group</h3></div>
    </div>

    @php
        $topAccountBracketCount = isset($accountAgeDistribution) && count($accountAgeDistribution)
            ? collect($accountAgeDistribution)->max('count') : 0;
        $maxAccountCount = $topAccountBracketCount ?: 1;
    @endphp

    <div class="age-chart">
        @forelse(($accountAgeDistribution ?? []) as $bracket)
            @php $pct = $bracket['count'] > 0 ? max(4, round(($bracket['count'] / $maxAccountCount) * 100)) : 0; @endphp
            <div class="age-row {{ $bracket['count'] === $topAccountBracketCount && $topAccountBracketCount > 0 ? 'is-top' : '' }}">
                <div class="age-label-wrap">
                    <span class="age-swatch"></span>
                    <span class="age-label">{{ $bracket['label'] }}</span>
                </div>
                <div class="age-track">
                    <div class="age-fill" data-pct="{{ $pct }}"></div>
                </div>
                <span class="age-val">{{ $bracket['count'] }}</span>
            </div>
        @empty
            <p style="color:var(--muted);font-size:12.5px;padding:20px 0;">Walang bagong account sa period na ito.</p>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
    <script>
        // Period filter — reloads with a query param; wire this to your
        // controller's date range logic.
        document.getElementById('periodFilter').addEventListener('change', function() {
            const days = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('days', days);
            window.location.href = url.toString();
        });

        // Animate the age-distribution bars in from 0 on load.
        requestAnimationFrame(() => {
            document.querySelectorAll('.age-fill').forEach(el => {
                const pct = el.getAttribute('data-pct') || 0;
                requestAnimationFrame(() => { el.style.width = pct + '%'; });
            });
        });

        // Expandable visitor login rows — fetch reservation details on demand.
        (function() {
            document.querySelectorAll('.visitor-row').forEach(row => {
                row.addEventListener('click', () => {
                    const panelRow = row.nextElementSibling;
                    const content = panelRow.querySelector('.reservation-panel-content');
                    const isOpen = row.classList.contains('is-open');

                    document.querySelectorAll('.visitor-row.is-open').forEach(openRow => {
                        if (openRow !== row) {
                            openRow.classList.remove('is-open');
                            openRow.nextElementSibling.classList.remove('is-open');
                        }
                    });

                    row.classList.toggle('is-open', !isOpen);
                    panelRow.classList.toggle('is-open', !isOpen);

                    if (isOpen || content.dataset.loaded) return;

                    const reservationId = row.dataset.reservationId;
                    if (!reservationId) {
                        content.innerHTML = '<p class="reservation-error">No linked reservation for this visitor.</p>';
                        content.dataset.loaded = '1';
                        return;
                    }

                    content.innerHTML = '<div class="reservation-loading"><span class="spinner"></span> Loading reservation details…</div>';

                    fetch(`/reservations/${reservationId}`, { headers: { 'Accept': 'application/json' } })
                        .then(res => {
                            if (!res.ok) throw new Error('Request failed');
                            return res.json();
                        })
                        .then(data => {
                            content.innerHTML = `
                                <div class="reservation-summary">
                                    <div class="reservation-field"><span class="k">Package</span><span class="v">${data.package ?? '—'}</span></div>
                                    <div class="reservation-field"><span class="k">Pax</span><span class="v">${data.pax ?? '—'}</span></div>
                                    <div class="reservation-field"><span class="k">Date</span><span class="v">${data.reservation_date ?? '—'}</span></div>
                                    <div class="reservation-field"><span class="k">Time</span><span class="v">${data.reservation_time ?? '—'}</span></div>
                                    <div class="reservation-field"><span class="k">Status</span><span class="v">${data.status ?? '—'}</span></div>
                                </div>
                                <a class="reservation-link-btn" href="/reservations#${reservationId}">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in Reservations
                                </a>
                            `;
                            content.dataset.loaded = '1';
                        })
                        .catch(() => {
                            content.innerHTML = '<p class="reservation-error">Couldn\'t load this reservation. Try again.</p>';
                        });
                });
            });
        })();
    </script>
    <script>
(function () {
    const wrap = document.querySelector('.service-pie-wrap');
    const tooltip = document.getElementById('servicePieTooltip');
    if (!wrap || !tooltip) return;

    const tooltipName = document.getElementById('tooltipName');
    const tooltipValue = document.getElementById('tooltipValue');
    const tooltipDot = document.getElementById('tooltipDot');

    function showTooltip(segment, clientX, clientY) {
        tooltipName.textContent = segment.dataset.name;
        tooltipValue.textContent = segment.dataset.count + ' bookings · ' + segment.dataset.percentage + '%';
        tooltipDot.style.background = segment.dataset.color;

        const rect = wrap.getBoundingClientRect();
        tooltip.style.left = (clientX - rect.left) + 'px';
        tooltip.style.top = (clientY - rect.top - 12) + 'px';
        tooltip.classList.add('is-visible');
    }

    function hideTooltip() {
        tooltip.classList.remove('is-visible');
    }

    document.querySelectorAll('.service-pie-segment').forEach(function (segment) {
        segment.addEventListener('mouseenter', (e) => showTooltip(segment, e.clientX, e.clientY));
        segment.addEventListener('mousemove', (e) => showTooltip(segment, e.clientX, e.clientY));
        segment.addEventListener('mouseleave', hideTooltip);
        segment.addEventListener('click', (e) => {
            e.stopPropagation();
            showTooltip(segment, e.clientX, e.clientY);
        });
    });

    document.addEventListener('click', hideTooltip);
})();
</script>

   <script>
        // Live visitor count + real-time traffic chart — poll every 15s
        const liveChartData = [];
        const MAX_POINTS = 30; // ~7.5 minutes of history at 15s interval

        function renderLiveChart() {
            const svg = document.getElementById('liveChartSvg');
            const emptyMsg = document.getElementById('liveChartEmpty');
            if (!svg) return;

            if (liveChartData.length < 2) {
                svg.innerHTML = '';
                if (emptyMsg) emptyMsg.style.display = 'flex';
                return;
            }
            if (emptyMsg) emptyMsg.style.display = 'none';

            const W = 600, H = 160, PAD = 10;
            const counts = liveChartData.map(d => d.count);
            const maxVal = Math.max(5, ...counts);
            const stepX = (W - PAD * 2) / (liveChartData.length - 1);

            const points = liveChartData.map((d, i) => {
                const x = PAD + i * stepX;
                const y = H - PAD - ((d.count / maxVal) * (H - PAD * 2));
                return `${x},${y}`;
            });

            const linePath = 'M' + points.join(' L');
            const lastX = PAD + (liveChartData.length - 1) * stepX;
            const lastY = H - PAD - ((counts[counts.length - 1] / maxVal) * (H - PAD * 2));
            const areaPath = `${linePath} L${lastX},${H - PAD} L${PAD},${H - PAD} Z`;

            svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
            svg.innerHTML = `
                <defs>
                    <linearGradient id="liveChartGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="var(--pink-deep)" stop-opacity="0.28"/>
                        <stop offset="100%" stop-color="var(--pink-deep)" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="${areaPath}" fill="url(#liveChartGrad)" stroke="none"></path>
                <path d="${linePath}" fill="none" stroke="var(--pink-deep)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <circle cx="${lastX}" cy="${lastY}" r="4.5" fill="var(--pink-deep)"></circle>
            `;
        }

        async function refreshLiveNow() {
            try {
                const res = await fetch("{{ route('analytics.live-count') }}");
                const data = await res.json();

                document.getElementById('liveNowValue').textContent = data.online_now;

                const currentNumEl = document.getElementById('liveChartCurrentNum');
                if (currentNumEl) currentNumEl.textContent = data.online_now;

                liveChartData.push({ t: new Date(), count: data.online_now });
                if (liveChartData.length > MAX_POINTS) liveChartData.shift();
                renderLiveChart();
            } catch (e) {
                console.error('Live count fetch failed', e);
            }
        }
        refreshLiveNow();
        setInterval(refreshLiveNow, 15000);
    </script>
@endpush