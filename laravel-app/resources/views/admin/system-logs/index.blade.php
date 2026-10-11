@extends('layouts.sidebar')

@section('title', 'System Logs')

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
    <style>
        /* ── Page header (title + stats in ONE compact card) ── */
        .page-head {
            background: var(--card);
            padding: 18px 24px;
            margin-bottom: 16px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-head .eyebrow {
            font-size: .66rem;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: 4px;
        }

        .page-head h2 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
        }

        .page-head .sub {
            font-size: 12px;
            color: var(--muted);
            margin-top: 3px;
        }

        .stat-strip {
            display: flex;
            align-items: stretch;
            flex-wrap: wrap;
            gap: 8px;
        }

        .stat-pill {
            display: flex;
            flex-direction: column;
            min-width: 92px;
            padding: 8px 14px;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: 12px;
        }

        .stat-pill .label {
            font-size: .62rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .stat-pill .value {
            font-family: 'Source Serif 4', serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.25;
        }

        .stat-pill .value.is-warn {
            color: #C98A1F;
        }

        .stat-pill .value.is-crit {
            color: var(--deduct);
        }

        .stat-note {
            align-self: center;
            font-size: 11px;
            color: var(--muted);
            padding-left: 4px;
        }

        /* ── Main card (the log table is the hero) ── */
        .table-box {
            background: var(--card);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .box-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 18px 22px 14px;
        }

        .box-head h3 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
        }

        .box-head-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .box-head .count {
            font-size: 12px;
            color: var(--muted);
        }

        /* ── Live indicator ── */
        .live-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 999px;
            background: #fff;
            color: var(--ink-soft);
            font-size: 11px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            user-select: none;
        }

        .live-pill:hover {
            border-color: var(--pink);
        }

        .live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2E9E5B;
            box-shadow: 0 0 0 0 rgba(46, 158, 91, .5);
            animation: livePulse 1.8s infinite;
        }

        .live-pill.paused .live-dot {
            background: var(--muted);
            animation: none;
        }

        .live-pill.error .live-dot {
            background: #D14343;
            animation: none;
        }

        .live-time {
            color: var(--muted);
            font-weight: 500;
        }

        @keyframes livePulse {
            0% {
                box-shadow: 0 0 0 0 rgba(46, 158, 91, .5);
            }

            70% {
                box-shadow: 0 0 0 7px rgba(46, 158, 91, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(46, 158, 91, 0);
            }
        }

        /* ── Filters (slim, one row) ── */
        .filter-bar {
            padding: 0 22px 16px;
            border-bottom: 1px solid var(--line);
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .filter-row .f {
            flex: 1 1 140px;
            min-width: 0;
        }

        .filter-row .f.search {
            flex: 2 1 220px;
        }

        .filter-row input[type="text"],
        .filter-row input[type="date"],
        .filter-row select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 12.5px;
            font-family: 'Inter', sans-serif;
            color: var(--ink-soft);
            background: var(--bg);
        }

        .filter-row input:focus,
        .filter-row select:focus {
            outline: none;
            border-color: var(--pink);
            background: #fff;
        }

        .filter-row select {
            cursor: pointer;
        }

        .chip-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
            white-space: nowrap;
        }

        .chip-btn:hover {
            border-color: var(--pink);
        }

        .chip-btn input {
            display: none;
        }

        .chip-btn.active {
            background: var(--deduct);
            border-color: var(--deduct);
            color: #fff;
        }

        .chip-btn.on {
            border-color: var(--pink-deep);
            color: var(--pink-deep);
        }

        .btn-primary {
            padding: 9px 18px;
            background: var(--pink-deep);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 12.5px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            transition: background .15s ease, box-shadow .15s ease;
        }

        .btn-primary:hover {
            background: var(--pink-dark);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        .filter-clear {
            color: var(--pink-deep);
            font-size: 12px;
            font-weight: 600;
            text-decoration: underline;
            font-family: 'Inter', sans-serif;
            padding: 9px 4px;
            white-space: nowrap;
        }

        .date-panel {
            display: none;
            gap: 8px;
            margin-top: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .date-panel.open {
            display: flex;
        }

        .date-panel .f {
            flex: 0 1 200px;
        }

        .date-panel span {
            font-size: 12px;
            color: var(--muted);
        }

        /* ── Table ── */
        .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .table-scroll::-webkit-scrollbar {
            display: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
            font-size: 12.5px;
            font-variant-numeric: tabular-nums;
        }

        th,
        td {
            padding: 13px 14px;
            text-align: left;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        /* Dark header */
        thead th {
            background: var(--ink);
            color: #fff;
            font-weight: 600;
            font-size: 12px;
            padding: 13px 14px;
            border-bottom: none;
            white-space: nowrap;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: var(--pink-pale);
        }

        /* New row highlight when live data arrives */
        tbody tr.row-new {
            animation: rowFlash 3.2s ease-out;
        }

        @keyframes rowFlash {
            0% {
                background: var(--pink-light);
            }

            100% {
                background: transparent;
            }
        }

        td {
            color: var(--ink-soft);
        }

        td.nowrap {
            white-space: nowrap;
        }

        td .time-date {
            color: var(--ink);
            font-weight: 600;
        }

        td .time-clock {
            display: block;
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
        }

        td.event {
            color: var(--ink);
            font-weight: 600;
        }

        td.desc {
            max-width: 340px;
            line-height: 1.5;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .02em;
            line-height: 1.4;
            text-transform: capitalize;
        }

        .badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
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
            text-transform: none;
        }

        .badge-anomaly::before {
            display: none;
        }

        .muted-dash {
            color: var(--muted);
        }

        .empty-row td {
            text-align: center;
            padding: 44px 20px !important;
            color: var(--muted);
        }

        .empty-row:hover {
            background: transparent;
        }

        /* ── Pagination ── */
        .pager-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 22px;
            border-top: 1px solid var(--line);
        }

        .pager-info {
            font-size: 12px;
            color: var(--muted);
        }

        .pager-info strong {
            color: var(--ink);
            font-weight: 600;
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
        }

        .pagination a,
        .pagination span.page {
            min-width: 32px;
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
            line-height: 1.2;
        }

        .pagination a:hover {
            border-color: var(--pink);
            color: var(--pink-deep);
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

        .pagination .dots {
            color: var(--muted);
            padding: 0 4px;
            font-size: 12px;
        }

        @media(max-width:900px) {
            .page-head h2 {
                font-size: 1.1rem;
            }

            table {
                min-width: 780px;
                font-size: 12px;
            }

            th,
            td {
                padding: 10px 10px;
            }
        }

        @media(max-width:600px) {
            .stat-strip {
                width: 100%;
            }

            .stat-pill {
                flex: 1 1 calc(50% - 8px);
            }

            .stat-note {
                display: none;
            }

            .box-head,
            .filter-bar,
            .pager-bar {
                padding-left: 14px;
                padding-right: 14px;
            }

            .pager-bar {
                justify-content: center;
            }

            .filter-row .f,
            .filter-row .f.search {
                flex: 1 1 100%;
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
        $filterKeys = ['severity', 'module', 'event_type', 'from', 'to', 'search', 'anomaly_only'];
        $hasFilters = collect(request()->only($filterKeys))->filter()->isNotEmpty();
        $hasDates = request()->filled('from') || request()->filled('to');
    @endphp

    {{-- ── Compact header: title + 24h stats in one card ── --}}
    <div class="page-head">
        <div>
            <div class="eyebrow">Admin &middot; Security</div>
            <h2>System Logs</h2>
            <p class="sub">Security and unusual events across the system.</p>
        </div>

        <div class="stat-strip" id="statStrip">
            <div class="stat-pill">
                <span class="label">Events</span>
                <span class="value">{{ $summary['total'] }}</span>
            </div>
            <div class="stat-pill">
                <span class="label">Anomalies</span>
                <span class="value {{ $summary['anomalies'] > 0 ? 'is-warn' : '' }}">{{ $summary['anomalies'] }}</span>
            </div>
            <div class="stat-pill">
                <span class="label">Critical</span>
                <span class="value {{ $summary['critical'] > 0 ? 'is-crit' : '' }}">{{ $summary['critical'] }}</span>
            </div>
            <div class="stat-pill">
                <span class="label">Failed logins</span>
                <span class="value">{{ $summary['failed_logins'] }}</span>
            </div>
            <span class="stat-note">Last 24 hrs</span>
        </div>
    </div>

    {{-- ── Log table card (main focus) ── --}}
    <div class="table-box">

        <div class="box-head">
            <h3>Log entries</h3>
            <div class="box-head-right">
                <span class="count" id="tableCount">
                    @if ($logs->total() > 0)
                        Showing {{ $logs->firstItem() }}&ndash;{{ $logs->lastItem() }} of {{ $logs->total() }}
                    @else
                        No entries
                    @endif
                </span>
                <button type="button" class="live-pill" id="livePill" title="Click to pause / resume auto-refresh">
                    <span class="live-dot"></span>
                    <span id="liveLabel">Live</span>
                    <span class="live-time" id="liveTime"></span>
                </button>
            </div>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="filter-bar">
            <div class="filter-row">
                <div class="f search">
                    <input type="text" name="search" id="fSearch" value="{{ request('search') }}"
                        placeholder="Search email, IP or text&hellip;" aria-label="Search">
                </div>

                <div class="f">
                    <select name="severity" id="fSeverity" aria-label="Severity">
                        <option value="">All severity</option>
                        @foreach (['info', 'warning', 'critical'] as $sev)
                            <option value="{{ $sev }}" @selected(request('severity') === $sev)>{{ ucfirst($sev) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="f">
                    <select name="module" id="fModule" aria-label="Module">
                        <option value="">All modules</option>
                        @foreach ($modules as $mod)
                            <option value="{{ $mod }}" @selected(request('module') === $mod)>
                                {{ ucfirst(str_replace(['_', '-'], ' ', $mod)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="f">
                    <select name="event_type" id="fEvent" aria-label="Event type">
                        <option value="">All events</option>
                        @foreach ($eventTypes as $type)
                            <option value="{{ $type }}" @selected(request('event_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" class="chip-btn {{ $hasDates ? 'on' : '' }}" id="dateToggle">
                    Date range
                </button>

                <label class="chip-btn {{ request('anomaly_only') ? 'active' : '' }}" id="anomalyToggle">
                    <input type="checkbox" name="anomaly_only" value="1" id="anomalyOnly"
                        @checked(request('anomaly_only'))>
                    Anomalies only
                </label>

                <button type="submit" class="btn-primary">Apply</button>

                @if ($hasFilters)
                    <a href="{{ url()->current() }}" class="filter-clear">Clear</a>
                @endif
            </div>

            <div class="date-panel {{ $hasDates ? 'open' : '' }}" id="datePanel">
                <div class="f">
                    <input type="date" name="from" id="fFrom" value="{{ request('from') }}" aria-label="From date">
                </div>
                <span>to</span>
                <div class="f">
                    <input type="date" name="to" id="fTo" value="{{ request('to') }}" aria-label="To date">
                </div>
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
                <tbody id="logBody">
                    @forelse ($logs as $log)
                        <tr data-id="{{ $log->id }}">
                            <td class="nowrap">
                                <span class="time-date">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="time-clock">{{ $log->created_at->format('h:i:s A') }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass[$log->severity] ?? 'badge-info' }}">{{ $log->severity }}</span>
                            </td>
                            <td>
                                @if ($log->module)
                                    {{ ucfirst(str_replace(['_', '-'], ' ', $log->module)) }}
                                @else
                                    <span class="muted-dash">—</span>
                                @endif
                            </td>
                            <td class="event">
                                {{ $log->event_type }}
                                @if ($log->is_anomaly)
                                    <span class="badge badge-anomaly">anomaly</span>
                                @endif
                            </td>
                            <td>
                                @if ($log->email)
                                    {{ $log->email }}
                                @else
                                    <span class="muted-dash">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($log->ip_address)
                                    {{ $log->ip_address }}
                                @else
                                    <span class="muted-dash">—</span>
                                @endif
                            </td>
                            <td class="desc">
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

        {{-- ── Pagination (wrapper always exists so live refresh can update it) ── --}}
        <div id="pagerWrap">
            @if ($logs->total() > 0)
                @php
                    $logs->withQueryString();
                    $current = $logs->currentPage();
                    $last = $logs->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp
                <div class="pager-bar">
                    <div class="pager-info">
                        Showing <strong>{{ $logs->firstItem() }}&ndash;{{ $logs->lastItem() }}</strong>
                        of <strong>{{ $logs->total() }}</strong> entries
                    </div>

                    @if ($logs->hasPages())
                        <div class="pagination">
                            @if ($logs->onFirstPage())
                                <span class="page disabled">&laquo; Prev</span>
                            @else
                                <a href="{{ $logs->previousPageUrl() }}">&laquo; Prev</a>
                            @endif

                            @if ($start > 1)
                                <a href="{{ $logs->url(1) }}">1</a>
                                @if ($start > 2)
                                    <span class="dots">&hellip;</span>
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
                                    <span class="dots">&hellip;</span>
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
            @endif
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        (function() {
            // ── "Anomalies only" pill: toggle the active look when clicked ──
            const toggle = document.getElementById('anomalyToggle');
            const box = document.getElementById('anomalyOnly');
            if (toggle && box) {
                box.addEventListener('change', () => toggle.classList.toggle('active', box.checked));
            }

            // ── "Date range" button: show/hide the From–To fields ──
            const dateBtn = document.getElementById('dateToggle');
            const datePanel = document.getElementById('datePanel');
            if (dateBtn && datePanel) {
                dateBtn.addEventListener('click', () => datePanel.classList.toggle('open'));
            }

            // ── LIVE AUTO-REFRESH ──
            // Every few seconds, kunin ulit ang current page (kasama filters + page number),
            // tapos palitan lang ang stats, table rows at pagination. Walang full reload,
            // kaya hindi nawawala ang tinatype mo sa filters o ang scroll position.
            const REFRESH_MS = 10000; // 10 seconds — pwede mong baguhin
            const PARTS = ['statStrip', 'tableCount', 'logBody', 'pagerWrap'];
            const pill = document.getElementById('livePill');
            const label = document.getElementById('liveLabel');
            const timeEl = document.getElementById('liveTime');

            let paused = false;
            let busy = false;
            let timer = null;

            function setState(state) {
                pill.classList.toggle('paused', state === 'paused');
                pill.classList.toggle('error', state === 'error');
                label.textContent = state === 'paused' ? 'Paused' : state === 'error' ? 'Offline' : 'Live';
            }

            function stamp() {
                timeEl.textContent = '· ' + new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }

            async function refresh() {
                if (busy || paused || document.hidden) return;
                busy = true;
                try {
                    const res = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');

                    // Kapag wala yung table sa response (hal. na-logout / na-redirect sa login)
                    if (!doc.getElementById('logBody')) throw new Error('Unexpected response');

                    // Alamin kung anong rows ang bago
                    const oldIds = new Set(
                        Array.from(document.querySelectorAll('#logBody tr[data-id]')).map(r => r.dataset.id)
                    );

                    let bodyChanged = false;
                    PARTS.forEach(id => {
                        const cur = document.getElementById(id);
                        const next = doc.getElementById(id);
                        if (!cur || !next) return;
                        if (cur.innerHTML.trim() !== next.innerHTML.trim()) {
                            cur.innerHTML = next.innerHTML;
                            if (id === 'logBody') bodyChanged = true;
                        }
                    });

                    // I-flash ang mga bagong pasok na rows
                    if (bodyChanged) {
                        document.querySelectorAll('#logBody tr[data-id]').forEach(row => {
                            if (!oldIds.has(row.dataset.id)) row.classList.add('row-new');
                        });
                    }

                    setState('live');
                    stamp();
                } catch (e) {
                    setState('error');
                } finally {
                    busy = false;
                }
            }

            function start() {
                clearInterval(timer);
                timer = setInterval(refresh, REFRESH_MS);
            }

            // Click the Live pill to pause / resume
            pill.addEventListener('click', () => {
                paused = !paused;
                setState(paused ? 'paused' : 'live');
                if (!paused) refresh();
            });

            // Pag bumalik ka sa tab, mag-refresh agad
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) refresh();
            });

            stamp();
            start();
        })();
    </script>
@endpush