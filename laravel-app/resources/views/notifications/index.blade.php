@extends('layouts.sidebar')

@section('title', 'Notifications')

@section('styles')
    <style>
        /* ===== Header ===== */
        .notif-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .notif-head-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notif-head-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            background: var(--pink-light);
            color: var(--pink-deep);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            flex-shrink: 0;
        }

        .notif-head h1 {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--ink);
            line-height: 1.2;
        }

        .notif-head-sub {
            font-size: .82rem;
            color: var(--ink-soft);
            margin-top: 2px;
        }

        .notif-head-sub strong {
            color: var(--pink-dark);
            font-weight: 700;
        }

        .notif-markall-form button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--pink);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .12s;
        }

        .notif-markall-form button:hover {
            background: var(--pink-dark);
        }

        /* ===== Toolbar: filters in one dense row ===== */
        .notif-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            background: var(--card);
            border-radius: 12px;
            padding: 6px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 16px;
        }

        .notif-filter-group {
            display: flex;
            gap: 3px;
        }

        .notif-filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            color: var(--ink-soft);
            transition: background .12s, color .12s;
            white-space: nowrap;
        }

        .notif-filter-pill:hover {
            background: var(--pink-pale);
            color: var(--ink);
        }

        .notif-filter-pill.active {
            background: var(--pink);
            color: #fff;
        }

        .notif-toolbar-divider {
            width: 1px;
            align-self: stretch;
            background: var(--line);
            margin: 4px 2px;
        }

        /* ===== Select all + bulk delete ===== */
        .notif-select-all-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 13px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            color: var(--ink-soft);
            cursor: pointer;
            white-space: nowrap;
        }

        .notif-select-all-pill input {
            width: 15px;
            height: 15px;
            accent-color: var(--pink);
            cursor: pointer;
        }

        .notif-bulk-bar {
            display: none;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            background: var(--card);
            border: 1px solid var(--pink-light);
            border-radius: 12px;
            padding: 10px 10px 10px 16px;
            margin-bottom: 16px;
            box-shadow: var(--shadow-sm);
        }

        .notif-bulk-bar.active {
            display: flex;
            animation: notifBulkIn .14s ease-out;
        }

        @keyframes notifBulkIn {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .notif-bulk-count {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: .82rem;
            font-weight: 700;
            color: var(--ink);
            white-space: nowrap;
        }

        .notif-bulk-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            background: var(--pink);
            color: #fff;
            font-size: .74rem;
            font-weight: 800;
        }

        .notif-bulk-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notif-bulk-actions button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: none;
            padding: 8px 14px;
            border-radius: 9px;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .12s, color .12s;
            white-space: nowrap;
        }

        .notif-bulk-btn-read {
            background: var(--pink-pale);
            color: var(--pink-dark);
        }

        .notif-bulk-btn-read:hover {
            background: var(--pink-light);
        }

        .notif-bulk-btn-delete {
            background: #FEE2E2;
            color: #DC2626;
        }

        .notif-bulk-btn-delete:hover {
            background: #FCA5A5;
        }

        .notif-bulk-clear {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--muted);
            font-size: .8rem;
            cursor: pointer;
            transition: background .12s, color .12s;
        }

        .notif-bulk-clear:hover {
            background: var(--line);
            color: var(--ink);
        }

        /* ===== Two-column layout: list + overview rail ===== */
        .notif-layout {
            display: grid;
            grid-template-columns: 1fr 268px;
            gap: 18px;
            align-items: start;
        }

        /* ---- List panel ---- */
        .notif-list-panel {
            background: var(--card);
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .notif-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            border-bottom: 1px solid var(--line);
            border-left: 3px solid transparent;
            transition: background .12s;
            cursor: pointer;
        }

        .notif-row:last-child {
            border-bottom: none;
        }

        .notif-row:hover,
        .notif-row:focus-visible {
            background: var(--pink-pale);
            outline: none;
        }

        .notif-row.unread {
            background: var(--pink-pale);
            border-left-color: var(--pink);
        }

        .notif-row-checkbox-wrap {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .notif-row-checkbox {
            width: 16px;
            height: 16px;
            accent-color: var(--pink);
            cursor: pointer;
        }

        .notif-row-icon {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .78rem;
        }

        .notif-row-icon.type-booking {
            background: var(--pink-light);
            color: var(--pink-deep);
        }

        .notif-row-icon.type-low_stock,
        .notif-row-icon.type-inventory {
            background: var(--amber-light);
            color: var(--amber);
        }

        .notif-row-icon.type-pos {
            background: #E3F0FF;
            color: #2563EB;
        }

        .notif-row-body {
            flex: 1;
            min-width: 0;
        }

        .notif-row-top {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 10px;
        }

        .notif-row-title {
            font-weight: 700;
            font-size: .86rem;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notif-row-time {
            font-size: .68rem;
            color: var(--muted);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .notif-row-msg {
            font-size: .78rem;
            color: var(--ink-soft);
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notif-row-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .notif-row-actions a,
        .notif-row-actions button {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: .78rem;
            background: transparent;
            color: var(--ink-soft);
            transition: background .12s, color .12s;
        }

        .notif-row-actions a:hover {
            background: var(--line);
            color: var(--ink);
        }

        .notif-row-actions button:hover {
            background: #FEE2E2;
            color: #DC2626;
        }

        /* ---- Overview rail ---- */
        .notif-rail {
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: sticky;
            top: 20px;
        }

        .notif-rail-card {
            background: var(--card);
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
            padding: 16px;
        }

        .notif-rail-title {
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .notif-rail-metric {
            margin-bottom: 12px;
        }

        .notif-rail-metric:last-child {
            margin-bottom: 0;
        }

        .notif-rail-metric-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .notif-rail-metric-label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .notif-rail-metric-label i {
            font-size: .7rem;
            width: 14px;
            text-align: center;
        }

        .notif-rail-metric-num {
            font-size: .82rem;
            font-weight: 800;
            color: var(--ink);
        }

        .notif-rail-bar-track {
            height: 6px;
            border-radius: 999px;
            background: var(--bg);
            overflow: hidden;
        }

        .notif-rail-bar-fill {
            height: 100%;
            border-radius: 999px;
        }

        .notif-rail-bar-fill.fill-unread { background: var(--pink); }
        .notif-rail-bar-fill.fill-booking { background: var(--pink-deep); }
        .notif-rail-bar-fill.fill-inventory { background: var(--amber); }
        .notif-rail-bar-fill.fill-pos { background: #2563EB; }

        .notif-rail-links {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .notif-rail-links a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 8px;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--ink-soft);
            transition: background .12s, color .12s;
        }

        .notif-rail-links a i:first-child {
            width: 15px;
            text-align: center;
            color: var(--muted);
        }

        .notif-rail-links a i:last-child {
            margin-left: auto;
            font-size: .68rem;
            color: var(--muted);
        }

        .notif-rail-links a:hover {
            background: var(--pink-pale);
            color: var(--pink-dark);
        }

        .notif-rail-links a:hover i {
            color: var(--pink-dark);
        }

        /* ---- Empty state ---- */
        .notif-empty {
            text-align: center;
            padding: 56px 20px;
            color: var(--ink-soft);
        }

        .notif-empty i {
            font-size: 1.8rem;
            color: var(--muted);
            margin-bottom: 10px;
            display: block;
        }

        .notif-empty-text {
            font-size: .88rem;
            font-weight: 700;
            color: var(--ink);
        }

        .notif-empty-sub {
            font-size: .8rem;
            margin-top: 4px;
        }

        .notif-empty-sub a {
            color: var(--pink-dark);
            font-weight: 700;
        }

        /* ---- Pagination ---- */
        .notif-pagination {
            margin-top: 16px;
        }

        .notif-pagination nav > div:first-child {
            display: none;
        }

        .notif-pagination ul {
            display: flex;
            gap: 4px;
            list-style: none;
            flex-wrap: wrap;
        }

        .notif-pagination a,
        .notif-pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            padding: 0 8px;
            border-radius: 8px;
            font-size: .78rem;
            font-weight: 600;
            color: var(--ink-soft);
            background: var(--card);
            box-shadow: var(--shadow-sm);
        }

        .notif-pagination .active span {
            background: var(--pink);
            color: #fff;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 2px solid var(--pink);
            outline-offset: 2px;
        }

        @media (max-width: 960px) {
            .notif-layout {
                grid-template-columns: 1fr;
            }

            .notif-rail {
                position: static;
            }
        }

        @media (max-width: 560px) {
            .notif-row-msg {
                white-space: normal;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $hasFilter = $status !== 'all' || $type !== 'all';
        $unreadPct    = $stats['total'] > 0 ? round($stats['unread']    / $stats['total'] * 100) : 0;
        $bookingPct   = $stats['total'] > 0 ? round($stats['booking']   / $stats['total'] * 100) : 0;
        $inventoryPct = $stats['total'] > 0 ? round($stats['inventory'] / $stats['total'] * 100) : 0;
        $posPct       = $stats['total'] > 0 ? round($stats['pos']       / $stats['total'] * 100) : 0;
    @endphp

    <div class="notif-head">
        <div class="notif-head-title">
            <div class="notif-head-icon"><i class="fa-solid fa-bell"></i></div>
            <div>
                <h1>Notifications</h1>
                <div class="notif-head-sub">
                    {{ $stats['total'] }} total
                    @if ($stats['unread'] > 0)
                        &middot; <strong>{{ $stats['unread'] }} unread</strong>
                    @else
                        &middot; all caught up
                    @endif
                </div>
            </div>
        </div>

        @if ($stats['unread'] > 0)
            <form class="notif-markall-form" method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit"><i class="fa-solid fa-check-double"></i> Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="notif-toolbar">
        <label class="notif-select-all-pill">
            <input type="checkbox" id="notifSelectAll">
            Select all
        </label>

        <div class="notif-toolbar-divider"></div>

        <div class="notif-filter-group">
            <a href="{{ route('notifications.index', array_filter(['type' => $type !== 'all' ? $type : null, 'status' => 'all'])) }}"
               class="notif-filter-pill {{ $status === 'all' ? 'active' : '' }}">All</a>
            <a href="{{ route('notifications.index', array_filter(['type' => $type !== 'all' ? $type : null, 'status' => 'unread'])) }}"
               class="notif-filter-pill {{ $status === 'unread' ? 'active' : '' }}">Unread</a>
            <a href="{{ route('notifications.index', array_filter(['type' => $type !== 'all' ? $type : null, 'status' => 'read'])) }}"
               class="notif-filter-pill {{ $status === 'read' ? 'active' : '' }}">Read</a>
        </div>

        <div class="notif-toolbar-divider"></div>

        <div class="notif-filter-group">
            <a href="{{ route('notifications.index', array_filter(['status' => $status !== 'all' ? $status : null, 'type' => 'all'])) }}"
               class="notif-filter-pill {{ $type === 'all' ? 'active' : '' }}">All types</a>
            <a href="{{ route('notifications.index', array_filter(['status' => $status !== 'all' ? $status : null, 'type' => 'booking'])) }}"
               class="notif-filter-pill {{ $type === 'booking' ? 'active' : '' }}"><i class="fa-solid fa-ticket"></i> Booking</a>
            <a href="{{ route('notifications.index', array_filter(['status' => $status !== 'all' ? $status : null, 'type' => 'inventory'])) }}"
               class="notif-filter-pill {{ $type === 'inventory' ? 'active' : '' }}"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
            <a href="{{ route('notifications.index', array_filter(['status' => $status !== 'all' ? $status : null, 'type' => 'pos'])) }}"
               class="notif-filter-pill {{ $type === 'pos' ? 'active' : '' }}"><i class="fa-solid fa-receipt"></i> POS</a>
        </div>
    </div>

    <div class="notif-bulk-bar" id="notifBulkBar">
        <div class="notif-bulk-count">
            <span class="notif-bulk-count-badge" id="notifBulkCount">0</span>
            selected
        </div>
        <div class="notif-bulk-actions">
            <button type="button" class="notif-bulk-btn-read" id="notifBulkReadBtn">
                <i class="fa-solid fa-check-double"></i> Mark as read
            </button>
            <button type="button" class="notif-bulk-btn-delete" id="notifBulkDeleteBtn">
                <i class="fa-solid fa-trash-can"></i> Delete
            </button>
            <button type="button" class="notif-bulk-clear" id="notifBulkClear" title="Clear selection" aria-label="Clear selection">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <form id="notifBulkReadForm" method="POST" action="{{ route('notifications.bulk-read') }}" style="display:none">
        @csrf
        @method('PATCH')
        <div id="notifBulkReadIds"></div>
    </form>

    <form id="notifBulkDeleteForm" method="POST" action="{{ route('notifications.bulk-destroy') }}" style="display:none">
        @csrf
        @method('DELETE')
        <div id="notifBulkDeleteIds"></div>
    </form>

    <div class="notif-layout">

        {{-- ===== List panel ===== --}}
        <div class="notif-list-panel">
            @if ($notifications->count() === 0)
                <div class="notif-empty">
                    <i class="fa-solid fa-bell-slash"></i>
                    <span class="notif-empty-text">
                        @if ($hasFilter)
                            No notifications match these filters.
                        @else
                            No notifications yet.
                        @endif
                    </span>
                    @if ($hasFilter)
                        <div class="notif-empty-sub">
                            <a href="{{ route('notifications.index') }}">Clear filters</a>
                        </div>
                    @else
                        <div class="notif-empty-sub">New bookings, inventory, and POS activity will show up here.</div>
                    @endif
                </div>
            @else
                @foreach ($notifications as $n)
                    <div class="notif-row {{ $n->is_read ? '' : 'unread' }}"
                         role="button" tabindex="0"
                         data-id="{{ $n->id }}"
                         data-title="{{ $n->title }}"
                         data-message="{{ $n->message }}"
                         data-time="{{ $n->time }}"
                         data-url="{{ $n->url ?: '#' }}"
                         data-type="{{ $n->type }}"
                         data-read-url="{{ route('notifications.read', $n->id) }}">
                        <div class="notif-row-checkbox-wrap">
                            <input type="checkbox" class="notif-row-checkbox" value="{{ $n->id }}" onclick="event.stopPropagation()">
                        </div>
                        <div class="notif-row-icon type-{{ $n->type }}">
                            <i class="fa-solid {{ in_array($n->type, ['low_stock', 'inventory']) ? 'fa-boxes-stacked' : ($n->type === 'pos' ? 'fa-receipt' : 'fa-ticket') }}"></i>
                        </div>
                        <div class="notif-row-body">
                            <div class="notif-row-top">
                                <span class="notif-row-title">{{ $n->title }}</span>
                                <span class="notif-row-time">{{ $n->time }}</span>
                            </div>
                            <div class="notif-row-msg">{{ $n->message }}</div>
                        </div>
                        <div class="notif-row-actions">
                            @if ($n->url)
                                <a href="{{ $n->url }}" title="Open" aria-label="Open">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            @endif
                            <form method="POST" action="{{ route('notifications.destroy', $n->id) }}"
                                  onsubmit="return confirm('Delete this notification? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete" aria-label="Delete" onclick="event.stopPropagation()">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- ===== Overview rail ===== --}}
        <div class="notif-rail">
            <div class="notif-rail-card">
                <div class="notif-rail-title">Overview</div>

                <div class="notif-rail-metric">
                    <div class="notif-rail-metric-row">
                        <span class="notif-rail-metric-label"><i class="fa-solid fa-circle-dot" style="color:var(--pink)"></i> Unread</span>
                        <span class="notif-rail-metric-num">{{ $stats['unread'] }}</span>
                    </div>
                    <div class="notif-rail-bar-track"><div class="notif-rail-bar-fill fill-unread" style="width:{{ $unreadPct }}%"></div></div>
                </div>

                <div class="notif-rail-metric">
                    <div class="notif-rail-metric-row">
                        <span class="notif-rail-metric-label"><i class="fa-solid fa-ticket" style="color:var(--pink-deep)"></i> Booking</span>
                        <span class="notif-rail-metric-num">{{ $stats['booking'] }}</span>
                    </div>
                    <div class="notif-rail-bar-track"><div class="notif-rail-bar-fill fill-booking" style="width:{{ $bookingPct }}%"></div></div>
                </div>

                <div class="notif-rail-metric">
                    <div class="notif-rail-metric-row">
                        <span class="notif-rail-metric-label"><i class="fa-solid fa-boxes-stacked" style="color:var(--amber)"></i> Inventory</span>
                        <span class="notif-rail-metric-num">{{ $stats['inventory'] }}</span>
                    </div>
                    <div class="notif-rail-bar-track"><div class="notif-rail-bar-fill fill-inventory" style="width:{{ $inventoryPct }}%"></div></div>
                </div>

                <div class="notif-rail-metric">
                    <div class="notif-rail-metric-row">
                        <span class="notif-rail-metric-label"><i class="fa-solid fa-receipt" style="color:#2563EB"></i> POS</span>
                        <span class="notif-rail-metric-num">{{ $stats['pos'] }}</span>
                    </div>
                    <div class="notif-rail-bar-track"><div class="notif-rail-bar-fill fill-pos" style="width:{{ $posPct }}%"></div></div>
                </div>
            </div>

            <div class="notif-rail-card">
                <div class="notif-rail-title">Quick Links</div>
                <div class="notif-rail-links">
                    <a href="{{ route('reservations.index') }}">
                        <i class="fa-solid fa-ticket"></i> Reservations <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    <a href="/inventory">
                        <i class="fa-solid fa-boxes-stacked"></i> Inventory <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    <a href="/pos">
                        <i class="fa-solid fa-receipt"></i> POS <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    @if ($notifications->count() > 0)
        <div class="notif-pagination">
            {{ $notifications->onEachSide(1)->links() }}
        </div>
    @endif

    <script>
    (function () {
    function init() {
    const selectAllBtn = document.getElementById('notifSelectAll');
    const bulkBar = document.getElementById('notifBulkBar');
    const bulkCountEl = document.getElementById('notifBulkCount');
    const clearBtn = document.getElementById('notifBulkClear');

    const readBtn = document.getElementById('notifBulkReadBtn');
    const readForm = document.getElementById('notifBulkReadForm');
    const readIdsContainer = document.getElementById('notifBulkReadIds');

    const deleteBtn = document.getElementById('notifBulkDeleteBtn');
    const deleteForm = document.getElementById('notifBulkDeleteForm');
    const deleteIdsContainer = document.getElementById('notifBulkDeleteIds');

    function getCheckboxes() {
        return document.querySelectorAll('.notif-row-checkbox');
    }

    function getCheckedIds() {
        return Array.from(getCheckboxes()).filter(cb => cb.checked).map(cb => cb.value);
    }

    function updateBulkBar() {
        const boxes = getCheckboxes();
        const checked = getCheckedIds();

        bulkBar.classList.toggle('active', checked.length > 0);
        bulkCountEl.textContent = checked.length;

        if (selectAllBtn) {
            selectAllBtn.checked = boxes.length > 0 && checked.length === boxes.length;
        }
    }

    function fillHiddenIds(container, ids) {
        container.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            container.appendChild(input);
        });
    }

    getCheckboxes().forEach(cb => cb.addEventListener('change', updateBulkBar));

    if (selectAllBtn) {
        selectAllBtn.addEventListener('change', function () {
            getCheckboxes().forEach(cb => { cb.checked = selectAllBtn.checked; });
            updateBulkBar();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            getCheckboxes().forEach(cb => { cb.checked = false; });
            updateBulkBar();
        });
    }

    if (readBtn) {
        readBtn.addEventListener('click', function () {
            const ids = getCheckedIds();
            if (ids.length === 0) return;

            fillHiddenIds(readIdsContainer, ids);
            readForm.submit();
        });
    }

    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            const ids = getCheckedIds();
            if (ids.length === 0) return;

            if (!confirm('Delete ' + ids.length + ' notification(s)? This cannot be undone.')) {
                return;
            }

            fillHiddenIds(deleteIdsContainer, ids);
            deleteForm.submit();
        });
    }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    })();
    </script>
@endsection