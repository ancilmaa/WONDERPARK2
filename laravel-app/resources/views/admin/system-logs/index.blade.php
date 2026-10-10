@extends('layouts.sidebar')

@section('title', 'System Logs')

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
    <style>
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

        .toolbar .sub {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }

        /* ── Stats ── */
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

        .stat-card .value.is-warn {
            color: #C98A1F;
        }

        .stat-card .value.is-crit {
            color: var(--deduct);
        }

        /* ── Filters ── */
        .filter-bar {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
            align-items: end;
            margin-bottom: 18px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--line);
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
        }

        .filter-field label {
            font-size: .68rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .filter-field input[type="text"],
        .filter-field input[type="date"],
        .filter-field select {
            padding: 9px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 12.5px;
            font-family: 'Inter', sans-serif;
            color: var(--ink-soft);
            background: var(--bg);
            width: 100%;
        }

        .filter-field input:focus,
        .filter-field select:focus {
            outline: none;
            border-color: var(--pink);
            background: #fff;
        }

        .filter-field select {
            cursor: pointer;
        }

        .filter-actions {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .filter-toggle {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 14px;
            border: 1px solid var(--line-strong);
            border-radius: 999px;
            background: #fff;
            color: var(--ink-soft);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all .15s ease;
            user-select: none;
        }

        .filter-toggle:hover {
            border-color: var(--pink);
        }

        .filter-toggle input {
            display: none;
        }

        .filter-toggle.active {
            background: var(--deduct);
            border-color: var(--deduct);
            color: #fff;
        }

        .filter-clear {
            color: var(--pink-deep);
            font-size: 12px;
            font-weight: 600;
            text-decoration: underline;
            font-family: 'Inter', sans-serif;
            padding: 9px 4px;
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

        /* ── Table ── */
        .table-box {
            background: var(--card);
            padding: 28px 26px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
        }

        .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .table-scroll::-webkit-scrollbar {
            display: none;
        }

        .log-header {
            text-align: center;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 2px solid var(--ink);
            position: relative;
        }

        .log-header::after {
            content: "";
            position: absolute;
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 1px;
            background: var(--pink);
        }

        .log-header h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 19px;
            font-weight: 700;
            letter-spacing: .6px;
            color: var(--ink);
        }

        .log-header p {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
            font-size: 12px;
            font-variant-numeric: tabular-nums;
        }

        th,
        td {
            padding: 11px 10px;
            text-align: left;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        thead th {
            background: var(--ink);
            color: #fff;
            font-weight: 600;
            font-size: 11px;
            padding: 10px;
            border-bottom: none;
        }

        tbody tr:hover {
            background: var(--pink-pale);
        }

        td {
            color: var(--ink-soft);
        }

        td.nowrap {
            white-space: nowrap;
        }

        td.event {
            color: var(--ink);
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .02em;
            line-height: 1.4;
        }

        .badge-info {
            background: var(--pink-light);
            color: var(--pink-deep);
        }

        .badge-warning {
            background: #FBEFD3;
            color: #8A5A0B;
        }

        .badge-critical {
            background: #FBE0E0;
            color: #B02020;
        }

        .badge-anomaly {
            background: var(--ink);
            color: #fff;
            margin-left: 6px;
        }

        .log-context {
            margin-top: 4px;
            font-size: 11px;
            color: var(--muted);
            word-break: break-all;
        }

        .empty-row td {
            text-align: center;
            padding: 30px !important;
            color: var(--muted);
        }

        .empty-row:hover {
            background: transparent;
        }

        /* ── Pagination ── */
        .pagination {
            display: flex;
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

        .pagination a,
        .pagination span.page {
            min-width: 30px;
            padding: 7px 10px;
            border: 1px solid var(--line-strong);
            background: #fff;
            color: var(--ink-soft);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            text-align: center;
            text-decoration: none;
        }

        .pagination a:hover {
            border-color: var(--pink);
        }

        .pagination .active {
            background: var(--pink-deep);
            border-color: var(--pink-deep);
            color: #fff;
        }

        .pagination .disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        @media(max-width:1100px) {
            .stats-row {
                grid-template-columns: 1fr 1fr;
            }

            .filter-bar {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(max-width:900px) {
            .toolbar h2 {
                font-size: 1.05rem;
            }

            table {
                min-width: 760px;
                font-size: 11px;
            }

            th,
            td {
                padding: 8px 6px;
            }
        }

        @media(max-width:600px) {
            .filter-bar {
                grid-template-columns: 1fr 1fr;
            }

            .log-header h3 {
                font-size: 14px;
            }
        }

        @media(max-width:420px) {
            .filter-bar {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $badgeClass = [
            'info' => 'badge-info',
            'warning' => 'badge-warning',
            'critical' => 'badge-critical',
        ];
        // Old log rows were saved in Tagalog. This translates them when shown.
        // Add more "Tagalog text" => "English text" pairs here as needed.
        $descriptionMap = [
            'Bawal na access:' => 'Unauthorized access:',
            'Maling password' => 'Incorrect password',
            'Nabigong login' => 'Failed login',
            'Matagumpay na login' => 'Successful login',
            'Walang logs na tugma sa filter.' => 'No logs match your filters.',
        ];
        $hasFilters = request()->hasAny(['severity', 'module', 'event_type', 'from', 'to', 'search', 'anomaly_only'])
            && collect(request()->only(['severity', 'module', 'event_type', 'from', 'to', 'search', 'anomaly_only']))->filter()->isNotEmpty();
    @endphp

    <div class="toolbar">
        <div>
            <div class="eyebrow">Admin &middot; Security</div>
            <h2>System Logs</h2>
            <p class="sub">Security and unusual events across the system.</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="label">Events</div>
            <div class="value">{{ $summary['total'] }}</div>
            <div class="sub">Last 24 hours</div>
        </div>
        <div class="stat-card">
            <div class="label">Anomalies</div>
            <div class="value {{ $summary['anomalies'] > 0 ? 'is-warn' : '' }}">{{ $summary['anomalies'] }}</div>
            <div class="sub">Last 24 hours</div>
        </div>
        <div class="stat-card">
            <div class="label">Critical</div>
            <div class="value {{ $summary['critical'] > 0 ? 'is-crit' : '' }}">{{ $summary['critical'] }}</div>
            <div class="sub">Last 24 hours</div>
        </div>
        <div class="stat-card">
            <div class="label">Failed Logins</div>
            <div class="value">{{ $summary['failed_logins'] }}</div>
            <div class="sub">Last 24 hours</div>
        </div>
    </div>

    <div class="table-box">

        <form method="GET" action="{{ url()->current() }}" class="filter-bar">
            <div class="filter-field">
                <label for="fSeverity">Severity</label>
                <select name="severity" id="fSeverity">
                    <option value="">All</option>
                    @foreach (['info', 'warning', 'critical'] as $sev)
                        <option value="{{ $sev }}" @selected(request('severity') === $sev)>{{ ucfirst($sev) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="fModule">Module</label>
                <select name="module" id="fModule">
                    <option value="">All modules</option>
                    @foreach ($modules as $mod)
                        <option value="{{ $mod }}" @selected(request('module') === $mod)>
                            {{ ucfirst(str_replace(['_', '-'], ' ', $mod)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="fEvent">Event type</label>
                <select name="event_type" id="fEvent">
                    <option value="">All</option>
                    @foreach ($eventTypes as $type)
                        <option value="{{ $type }}" @selected(request('event_type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="fFrom">From</label>
                <input type="date" name="from" id="fFrom" value="{{ request('from') }}">
            </div>

            <div class="filter-field">
                <label for="fTo">To</label>
                <input type="date" name="to" id="fTo" value="{{ request('to') }}">
            </div>

            <div class="filter-field">
                <label for="fSearch">Search</label>
                <input type="text" name="search" id="fSearch" value="{{ request('search') }}"
                    placeholder="Email, IP or text&hellip;">
            </div>

            <div class="filter-actions">
                <label class="filter-toggle {{ request('anomaly_only') ? 'active' : '' }}" id="anomalyToggle">
                    <input type="checkbox" name="anomaly_only" value="1" id="anomalyOnly"
                        @checked(request('anomaly_only'))>
                    Anomalies only
                </label>
                <button type="submit" class="btn-primary">Apply filters</button>
                @if ($hasFilters)
                    <a href="{{ url()->current() }}" class="filter-clear">Clear filters</a>
                @endif
            </div>
        </form>

        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Severity</th>
                        <th>Module</th>
                        <th>Event</th>
                        <th>User</th>
                        <th>IP</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="nowrap">{{ $log->created_at->format('M d, Y h:i:s A') }}</td>
                            <td>
                                <span class="badge {{ $badgeClass[$log->severity] ?? 'badge-info' }}">{{ $log->severity }}</span>
                            </td>
                            <td>{{ $log->module ? ucfirst(str_replace(['_', '-'], ' ', $log->module)) : '—' }}</td>
                            <td class="event">
                                {{ $log->event_type }}
                                @if ($log->is_anomaly)
                                    <span class="badge badge-anomaly">anomaly</span>
                                @endif
                            </td>
                            <td>{{ $log->email ?? '—' }}</td>
                            <td>{{ $log->ip_address ?? '—' }}</td>
                            <td>
                                {{ str_replace(array_keys($descriptionMap), array_values($descriptionMap), $log->description) }}
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="7">No logs match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            @php
                $logs->withQueryString();
                $current = $logs->currentPage();
                $last = $logs->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
            @endphp
            <div class="pagination">
                <span class="pagination-info">Page {{ $current }} of {{ $last }}</span>

                @if ($logs->onFirstPage())
                    <span class="page disabled">&laquo; Prev</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}">&laquo; Prev</a>
                @endif

                @if ($start > 1)
                    <a href="{{ $logs->url(1) }}">1</a>
                    @if ($start > 2)
                        <span class="pagination-info">&hellip;</span>
                    @endif
                @endif

                @for ($p = $start; $p <= $end; $p++)
                    @if ($p === $current)
                        <span class="page active">{{ $p }}</span>
                    @else
                        <a href="{{ $logs->url($p) }}">{{ $p }}</a>
                    @endif
                @endfor

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <span class="pagination-info">&hellip;</span>
                    @endif
                    <a href="{{ $logs->url($last) }}">{{ $last }}</a>
                @endif

                @if ($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}">Next &raquo;</a>
                @else
                    <span class="page disabled">Next &raquo;</span>
                @endif
            </div>
        @endif

    </div>

@endsection

@push('scripts')
    <script>
        // "Anomalies only" pill: toggle the active look when clicked.
        (function() {
            const toggle = document.getElementById('anomalyToggle');
            const box = document.getElementById('anomalyOnly');
            if (!toggle || !box) return;
            box.addEventListener('change', () => toggle.classList.toggle('active', box.checked));
        })();
    </script>
@endpush