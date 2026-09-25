@extends('layouts.sidebar')

@section('title', 'Notifications')

@section('styles')
    <style>
        /* ===== Top summary strip ===== */
        .notif-topstrip {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .78rem;
            color: var(--ink-soft);
            margin-bottom: 14px;
        }

        .notif-topstrip-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--pink-pale);
            color: var(--pink-dark);
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .notif-topstrip-pill .dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: var(--pink);
            flex-shrink: 0;
        }

        .notif-topstrip-sep {
            color: var(--muted);
        }

        /* ===== Header ===== */
        .notif-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .notif-head-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .notif-head-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--pink-light);
            color: var(--pink-deep);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .notif-head h1 {
            font-size: 1.35rem;
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

        .notif-head-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notif-search {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--card);
            box-shadow: var(--shadow-sm);
            border-radius: 10px;
            padding: 9px 14px;
            min-width: 220px;
        }

        .notif-search i {
            color: var(--muted);
            font-size: .8rem;
        }

        .notif-search input {
            border: none;
            outline: none;
            background: transparent;
            font-size: .8rem;
            color: var(--ink);
            width: 100%;
        }

        .notif-search input::placeholder {
            color: var(--muted);
        }

        .notif-iconbtn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--card);
            box-shadow: var(--shadow-sm);
            color: var(--ink-soft);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
            flex-shrink: 0;
            transition: background .12s, color .12s;
        }

        .notif-iconbtn:hover {
            background: var(--pink-pale);
            color: var(--pink-dark);
        }

        .notif-markall-form button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--pink);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: .82rem;
            font-weight: 700;
            cursor: pointer;
            transition: background .12s;
            white-space: nowrap;
        }

        .notif-markall-form button:hover {
            background: var(--pink-dark);
        }

        /* ===== Toolbar ===== */
        .notif-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .notif-toolbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        /* Status segmented control */
        .notif-segment {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            background: var(--card);
            box-shadow: var(--shadow-sm);
            border-radius: 10px;
            padding: 4px;
        }

        .notif-segment a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 7px;
            font-size: .8rem;
            font-weight: 700;
            color: var(--ink-soft);
            white-space: nowrap;
            transition: background .12s, color .12s;
        }

        .notif-segment a:hover {
            color: var(--ink);
        }

        .notif-segment a.active {
            background: var(--ink);
            color: #fff;
        }

        .notif-segment a .count {
            font-weight: 800;
            opacity: .75;
        }

        .notif-segment a.active .count {
            opacity: .9;
        }

        /* Category tabs */
        .notif-tabs {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .notif-tabs a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--ink-soft);
            white-space: nowrap;
            transition: background .12s, color .12s;
        }

        .notif-tabs a:hover {
            background: var(--pink-pale);
            color: var(--ink);
        }

        .notif-tabs a.active {
            background: var(--pink-pale);
            color: var(--pink-dark);
        }

        .notif-toolbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .notif-select-all-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: .8rem;
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

        .notif-markread-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: none;
            border: none;
            font-size: .8rem;
            font-weight: 700;
            color: var(--ink-soft);
            cursor: pointer;
            padding: 6px 4px;
            transition: color .12s;
        }

        .notif-markread-btn:hover {
            color: var(--pink-dark);
        }

        /* ===== Bulk bar (shown once a row is checked) ===== */
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

        /* ===== List panel ===== */
        .notif-list-panel {
            background: var(--card);
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .notif-row {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 15px 18px;
            border-bottom: 1px solid var(--line);
            transition: background .12s;
            cursor: pointer;
        }

        .notif-row:last-child {
            border-bottom: none;
        }

        .notif-row:hover,
        .notif-row:focus-visible {
            background: var(--bg);
            outline: none;
        }

        .notif-row.unread {
            background: var(--pink-pale);
        }

        .notif-row.unread:hover,
        .notif-row.unread:focus-visible {
            background: var(--pink-light);
        }

        .notif-row-checkbox-wrap {
            display: flex;
            align-items: center;
            height: 22px;
            flex-shrink: 0;
        }

        .notif-row-checkbox {
            width: 15px;
            height: 15px;
            accent-color: var(--pink);
            cursor: pointer;
        }

        .notif-row-dot-wrap {
            width: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 22px;
            flex-shrink: 0;
        }

        .notif-row-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: var(--pink);
        }

        .notif-row-body {
            flex: 1;
            min-width: 0;
        }

        .notif-row-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .notif-row-title-group {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
        }

        .notif-row-title {
            font-weight: 700;
            font-size: .9rem;
            color: var(--ink);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notif-badge {
            flex-shrink: 0;
            font-size: .68rem;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .notif-badge.type-booking {
            background: var(--pink-light);
            color: var(--pink-deep);
        }

        .notif-badge.type-low_stock,
        .notif-badge.type-inventory {
            background: var(--amber-light);
            color: var(--amber);
        }

        .notif-badge.type-pos {
            background: #E3F0FF;
            color: #2563EB;
        }

        .notif-badge.type-default {
            background: var(--line);
            color: var(--ink-soft);
        }

        .notif-row-time {
            font-size: .72rem;
            color: var(--muted);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .notif-row-msg {
            font-size: .8rem;
            color: var(--ink-soft);
            margin-top: 4px;
            line-height: 1.45;
        }

        .notif-row-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
            font-size: .76rem;
        }

        .notif-row-meta a {
            color: var(--pink-dark);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .notif-row-meta a:hover {
            text-decoration: underline;
        }

        .notif-row-meta-dot {
            color: var(--muted);
        }

        .notif-row-meta-extra {
            color: var(--muted);
        }

        .notif-row-delete {
            flex-shrink: 0;
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
            color: var(--muted);
            transition: background .12s, color .12s;
        }

        .notif-row-delete:hover {
            background: #FEE2E2;
            color: #DC2626;
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

        /* ---- Footer: pagination + status strip ---- */
        .notif-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .notif-footer-count {
            font-size: .8rem;
            color: var(--ink-soft);
        }

        .notif-footer-count strong {
            color: var(--ink);
            font-weight: 700;
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

        .notif-statusbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 22px;
            padding-top: 14px;
            border-top: 1px solid var(--line);
            font-size: .72rem;
            color: var(--muted);
        }

        .notif-statusbar-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notif-statusbar-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #22C55E;
            flex-shrink: 0;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 2px solid var(--pink);
            outline-offset: 2px;
        }

        @media (max-width: 900px) {
            .notif-head-right {
                width: 100%;
            }

            .notif-search {
                flex: 1;
            }

            .notif-toolbar-right {
                width: 100%;
                justify-content: space-between;
            }
        }

        @media (max-width: 560px) {
            .notif-row-top {
                flex-wrap: wrap;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $hasFilter = $status !== 'all' || $type !== 'all';
        $readCount = max($stats['total'] - $stats['unread'], 0);
    @endphp

    {{-- ===== Top summary strip ===== --}}
    <div class="notif-topstrip">
        <span class="notif-topstrip-pill">
            <span class="dot"></span>
            {{ $stats['unread'] }} unread alert{{ $stats['unread'] === 1 ? '' : 's' }} needing attention
        </span>
        <span class="notif-topstrip-sep">&middot;</span>
        <span>{{ $readCount }} resolved</span>
    </div>

    {{-- ===== Header ===== --}}
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

        <div class="notif-head-right">
            <div class="notif-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search notifications, tickets, &amp; invoices" disabled>
            </div>
            <button type="button" class="notif-iconbtn" title="Export" aria-label="Export" disabled>
                <i class="fa-solid fa-download"></i>
            </button>
            @if ($stats['unread'] > 0)
                <form class="notif-markall-form" method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit"><i class="fa-solid fa-check-double"></i> Mark all as read</button>
                </form>
            @endif
        </div>
    </div>

    {{-- ===== Toolbar ===== --}}
    <div class="notif-toolbar">
        <div class="notif-toolbar-left">
            <div class="notif-segment">
                <a href="{{ route('notifications.index', array_filter(['type' => $type !== 'all' ? $type : null, 'status' => 'all'])) }}"
                   class="{{ $status === 'all' ? 'active' : '' }}">All <span class="count">({{ $stats['total'] }})</span></a>
                <a href="{{ route('notifications.index', array_filter(['type' => $type !== 'all' ? $type : null, 'status' => 'unread'])) }}"
                   class="{{ $status === 'unread' ? 'active' : '' }}">Unread <span class="count">{{ $stats['unread'] }}</span></a>
                <a href="{{ route('notifications.index', array_filter(['type' => $type !== 'all' ? $type : null, 'status' => 'read'])) }}"
                   class="{{ $status === 'read' ? 'active' : '' }}">Read</a>
            </div>

            <div class="notif-tabs">
                <a href="{{ route('notifications.index', array_filter(['status' => $status !== 'all' ? $status : null, 'type' => 'all'])) }}"
                   class="{{ $type === 'all' ? 'active' : '' }}">All categories</a>
                <a href="{{ route('notifications.index', array_filter(['status' => $status !== 'all' ? $status : null, 'type' => 'booking'])) }}"
                   class="{{ $type === 'booking' ? 'active' : '' }}">Bookings</a>
                <a href="{{ route('notifications.index', array_filter(['status' => $status !== 'all' ? $status : null, 'type' => 'inventory'])) }}"
                   class="{{ $type === 'inventory' ? 'active' : '' }}">Inventory</a>
                <a href="{{ route('notifications.index', array_filter(['status' => $status !== 'all' ? $status : null, 'type' => 'pos'])) }}"
                   class="{{ $type === 'pos' ? 'active' : '' }}">POS</a>
            </div>
        </div>

        <div class="notif-toolbar-right">
            <label class="notif-select-all-pill">
                <input type="checkbox" id="notifSelectAll">
                Select all
            </label>
            <button type="button" class="notif-markread-btn" id="notifToolbarReadBtn">Mark read</button>
        </div>
    </div>

    {{-- ===== Bulk bar (delete + read, shown once rows are checked) ===== --}}
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
                @php
                    $badgeClass = in_array($n->type, ['booking', 'low_stock', 'inventory', 'pos'])
                        ? 'type-' . $n->type
                        : 'type-default';
                @endphp
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
                    <div class="notif-row-dot-wrap">
                        @unless ($n->is_read)
                            <span class="notif-row-dot"></span>
                        @endunless
                    </div>
                    <div class="notif-row-body">
                        <div class="notif-row-top">
                            <div class="notif-row-title-group">
                                <span class="notif-row-title">{{ $n->title }}</span>
                                <span class="notif-badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $n->type)) }}</span>
                            </div>
                            <span class="notif-row-time">{{ $n->time }}</span>
                        </div>
                        <div class="notif-row-msg">{{ $n->message }}</div>
                        @if ($n->url)
                            <div class="notif-row-meta">
                                <a href="{{ $n->url }}" onclick="event.stopPropagation()">
                                    View details <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('notifications.destroy', $n->id) }}"
                          onsubmit="return confirm('Delete this notification? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="notif-row-delete" title="Delete" aria-label="Delete" onclick="event.stopPropagation()">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        @endif
    </div>

    {{-- ===== Footer: pagination ===== --}}
    @if ($notifications->count() > 0)
        <div class="notif-footer">
            <div class="notif-footer-count">
                Showing <strong>{{ $notifications->firstItem() }}</strong> to <strong>{{ $notifications->lastItem() }}</strong>
                of <strong>{{ $notifications->total() }}</strong> results
            </div>
            <div class="notif-pagination">
                {{ $notifications->onEachSide(1)->links() }}
            </div>
        </div>
    @endif

    {{-- ===== Bottom status strip ===== --}}
    <div class="notif-statusbar">
        <div class="notif-statusbar-left">
            <span>WonderParkCoreOS</span>
            <span>&middot;</span>
            <span>Lipa Branch Operations</span>
            <span>&middot;</span>
            <span class="notif-statusbar-dot"></span>
            <span>Synced</span>
        </div>
        <div>&copy; {{ date('Y') }} WonderPark Corporation. All rights reserved.</div>
    </div>

    <script>
    (function () {
    function init() {
    const selectAllBtn = document.getElementById('notifSelectAll');
    const bulkBar = document.getElementById('notifBulkBar');
    const bulkCountEl = document.getElementById('notifBulkCount');
    const clearBtn = document.getElementById('notifBulkClear');

    const toolbarReadBtn = document.getElementById('notifToolbarReadBtn');
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

    function submitRead() {
        const ids = getCheckedIds();
        if (ids.length === 0) return;

        fillHiddenIds(readIdsContainer, ids);
        readForm.submit();
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

    if (toolbarReadBtn) {
        toolbarReadBtn.addEventListener('click', submitRead);
    }

    if (readBtn) {
        readBtn.addEventListener('click', submitRead);
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