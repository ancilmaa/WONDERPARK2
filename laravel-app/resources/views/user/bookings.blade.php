{{--
    resources/views/user/bookings.blade.php
    Route: GET  /user/bookings                        -> user.bookings
           POST /user/bookings/{booking}/cancel        -> user.bookings.cancel
           POST /user/bookings/{booking}/reschedule    -> user.bookings.reschedule
    Controller: App\Http\Controllers\User\BookingController@index / cancel / reschedule

    Expects from the controller:
      $bookings -> LengthAwarePaginator of
        ['id', 'package', 'date', 'pax', 'status_label', 'status_class']
        status_class is one of: green | amber | rose

      PARA SA REJECTED (bago):
        'approval_status' => 'rejected' (o 'pending' / 'approved')
        'reject_reason'   => string|null
        'reject_note'     => string|null
--}}
@extends('layouts.user')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')
@section('page-subtitle', 'Manage your reservations')
@section('body-class', 'page-uniform')

@section('content')

    <style>
        /* ===== Clickable "Rejected" tag + reason popup ===== */
        .tag.tag-clickable {
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            letter-spacing: inherit;
            text-transform: inherit;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: filter .15s, box-shadow .15s;
        }
        .tag.tag-clickable:hover { filter: brightness(.95); box-shadow: 0 0 0 3px rgba(226,75,74,.15); }
        .tag.tag-clickable:focus-visible { outline: 2px solid #e24b4a; outline-offset: 2px; }
        .reject-hint { font-size: 11px; color: var(--muted); font-style: italic; }

        #rejectReasonOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            align-items: center;
            justify-content: center;
            background: rgba(20, 16, 30, .5);
            padding: 20px;
        }
        #rejectReasonOverlay.active { display: flex; }

        .rr-card {
            background: #fff;
            width: 100%;
            max-width: 400px;
            border-radius: 16px;
            padding: 24px 22px 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }
        .rr-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #FDE8ED;
            color: #DC2650;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 700;
            margin: 0 auto 12px;
        }
        .rr-card h3 { text-align: center; margin: 0 0 4px; font-size: 18px; }
        .rr-sub { text-align: center; margin: 0 0 16px; font-size: 12.5px; color: var(--muted); }
        .rr-box {
            background: #FDE8ED;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 10px;
        }
        .rr-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #DC2650;
            margin-bottom: 4px;
        }
        .rr-text { font-size: 14px; font-weight: 600; color: #3b1020; line-height: 1.45; word-break: break-word; }
        .rr-note { font-size: 13px; font-weight: 500; }
        .rr-help { margin: 12px 0 16px; font-size: 12px; color: var(--muted); text-align: center; line-height: 1.5; }
        .rr-actions { display: flex; gap: 8px; }
        .rr-actions a, .rr-actions button { flex: 1; text-align: center; }
    </style>

    @if (isset($stats) && $stats['total_visits'] > 0)
        <div class="mb-tabs" id="mbTabs">
            <button type="button" class="mb-tab active" data-target="bookingsPanel">My Bookings</button>
            <button type="button" class="mb-tab" data-target="analyticsPanel">Analytics</button>
        </div>
    @endif

    <div class="u-card" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <h4 style="margin:0 0 4px;">My Bookings</h4>
            <p style="margin:0;">Manage upcoming &amp; past visits</p>
        </div>
        <a href="{{ route('user.booking') }}"
           class="u-btn ghost"
           style="width:auto;padding:9px 16px;font-size:12px;white-space:nowrap;">
            + New Booking
        </a>
    </div>

    @if (isset($stats) && $stats['total_visits'] > 0)
        <div id="analyticsPanel" class="mb-panel" hidden>
        <div class="u-card" style="display:flex;gap:0;text-align:center;padding:16px 8px;">
            <div style="flex:1;">
                <div style="font-size:22px;font-weight:700;">{{ $stats['total_visits'] }}</div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;">Total Visits</div>
            </div>
            <div style="flex:1;border-left:1px solid var(--border,#eee);border-right:1px solid var(--border,#eee);">
                <div style="font-size:22px;font-weight:700;">{{ $stats['upcoming_reservations'] }}</div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;">Upcoming</div>
            </div>
            <div style="flex:1;">
                <div style="font-size:22px;font-weight:700;">{{ $stats['completed_reservations'] }}</div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;">Completed</div>
            </div>
        </div>

        <div class="u-card" style="margin-top:14px;display:flex;gap:0;text-align:center;padding:16px 8px;">
            <div style="flex:1;">
                <div style="font-size:22px;font-weight:700;">₱{{ number_format($stats['total_spending'], 0) }}</div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;">Total Spending</div>
            </div>
            <div style="flex:1;border-left:1px solid var(--border,#eee);">
                <div style="font-size:15px;font-weight:700;line-height:1.3;">{{ $stats['favorite_package'] }}</div>
                <div style="font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;">Most Booked ({{ $stats['favorite_package_count'] }}x)</div>
            </div>
        </div>

        <div style="display:flex;gap:14px;margin-top:14px;flex-wrap:wrap;">
            <div class="u-card" style="flex:1;min-width:280px;margin-top:0;display:flex;flex-direction:column;">
                <h4 style="margin-bottom:10px;">Visit History, last 6 months</h4>
                <div style="flex:1;min-height:260px;max-height:260px;">
                    <canvas id="historyChart"></canvas>
                </div>
            </div>

            <div class="u-card" style="flex:1;min-width:280px;margin-top:0;display:flex;flex-direction:column;">
                <h4 style="margin-bottom:12px;">Bookings by Attraction</h4>
                <div style="flex:1;min-height:260px;max-height:260px;display:flex;align-items:center;justify-content:center;">
                    <canvas id="categoryChart"></canvas>
                </div>
                <div id="categoryLegend" style="display:flex;flex-wrap:wrap;gap:14px;justify-content:center;margin-top:10px;font-size:13px;"></div>
            </div>
        </div>
        </div>
    @endif

    <div id="bookingsPanel" class="mb-panel">
    <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;">
        @forelse ($bookings as $booking)
            @php
                // Nireject ba ng admin?
                $isRejected = ($booking['approval_status'] ?? null) === 'rejected'
                    || ($booking['status'] ?? null) === 'rejected'
                    || strtolower($booking['status_label'] ?? '') === 'rejected';
            @endphp
            <div class="mb-row u-card" style="margin-top:0;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;padding:18px 20px;">
                <div style="flex:1;min-width:220px;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px;">
                        <span style="font-size:10.5px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);">{{ $booking['category'] }}</span>

                        @if ($isRejected)
                            {{-- Clickable: lalabas ang rason ng admin --}}
                            <button type="button"
                                    class="tag {{ $booking['status_class'] }} tag-clickable js-show-reject"
                                    style="white-space:nowrap;"
                                    title="Click to see why"
                                    data-package="{{ $booking['package'] }}"
                                    data-date="{{ $booking['date'] }}"
                                    data-reason="{{ $booking['reject_reason'] ?? '' }}"
                                    data-note="{{ $booking['reject_note'] ?? '' }}">
                                {{ $booking['status_label'] }} <span aria-hidden="true">&#9432;</span>
                            </button>
                        @else
                            <span class="tag {{ $booking['status_class'] }}" style="white-space:nowrap;">{{ $booking['status_label'] }}</span>
                        @endif
                    </div>
                    <h4 style="margin:0 0 4px;">{{ $booking['package'] }}</h4>
                    <p style="margin:0;font-size:13px;color:var(--muted);">{{ $booking['date'] }} &middot; {{ $booking['pax'] }} pax</p>

                    @if ($booking['status_class'] === 'green' && !empty($booking['voucher_code']))
                        <div style="margin-top:10px;background:var(--bg,#f8f6f9);border-radius:10px;padding:8px 12px;display:inline-block;">
                            <span style="font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-right:6px;">Voucher Code</span>
                            <span style="font-family:monospace;font-size:13px;font-weight:700;letter-spacing:.5px;">{{ $booking['voucher_code'] }}</span>
                        </div>
                    @endif
                </div>

                <div class="mb-actions" style="min-width:150px;">
                    @if (in_array($booking['status'], ['confirmed', 'done'], true))
                        {{-- Approved by the admin: schedule is locked, no more reschedule/cancel --}}
                        <p style="margin:0;font-size:11.5px;line-height:1.5;color:var(--muted);text-align:center;">
                            @if ($booking['status'] === 'confirmed')
                                Approved &middot; rescheduling is no longer available
                            @else
                                Voucher already used
                            @endif
                        </p>
                    @elseif ($booking['status'] === 'awaiting_verification')
                        {{-- Proof sent: waiting for the admin. Can still reschedule or cancel until approved. --}}
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <p style="margin:0;font-size:11.5px;line-height:1.5;color:var(--muted);">
                                We're checking your payment. You can still reschedule until it's approved.
                            </p>
                            <div style="display:flex;gap:8px;">
                                <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}"
                                   class="u-btn ghost"
                                   style="flex:1;padding:8px 6px;font-size:11px;font-weight:600;text-align:center;">
                                    Reschedule
                                </a>

                                <form method="POST"
                                      action="{{ route('user.bookings.cancel', $booking['id']) }}"
                                      style="flex:1;"
                                      onsubmit="return confirm('Cancel this booking?');">
                                    @csrf
                                    <button type="submit"
                                            class="u-btn ghost"
                                            style="width:100%;padding:8px 6px;font-size:11px;font-weight:600;color:#e24b4a;border-color:#e24b4a;">
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        </div>
                    @elseif ($booking['status'] === 'pending_payment')
                        <div style="display:flex;flex-direction:column;gap:8px;">

                            @if (!empty($booking['rejection_reason']))
                                <p style="margin:0;font-size:11.5px;line-height:1.5;color:#e24b4a;">
                                    Your payment proof was not accepted: {{ $booking['rejection_reason'] }}. Please upload a new proof.
                                </p>
                            @endif

                            <a href="{{ route('user.bookings.review', $booking['id']) }}"
                               class="u-btn"
                               style="width:100%;padding:10px;font-size:12px;font-weight:600;text-align:center;">
                                {{ !empty($booking['rejection_reason']) ? 'Upload New Proof' : 'Review Booking' }}
                            </a>

                            <div style="display:flex;gap:8px;">
                                <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}"
                                   class="u-btn ghost"
                                   style="flex:1;padding:8px 6px;font-size:11px;font-weight:600;text-align:center;">
                                    Reschedule
                                </a>

                                <form method="POST"
                                      action="{{ route('user.bookings.cancel', $booking['id']) }}"
                                      style="flex:1;"
                                      onsubmit="return confirm('Cancel this booking?');">
                                    @csrf
                                    <button type="submit"
                                            class="u-btn ghost"
                                            style="width:100%;padding:8px 6px;font-size:11px;font-weight:600;color:#e24b4a;border-color:#e24b4a;">
                                        Cancel
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="u-card" style="margin-top:0;text-align:center;padding:24px 20px;font-size:13px;color:var(--muted);">
                You don't have any bookings yet.
            </div>
        @endforelse
    </div>

    {{-- Custom pagination, styled to match the .pg-btn theme already used for packages --}}
    @if ($bookings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $bookings->hasPages())
        <div class="promo-pagination" style="margin-top:16px;justify-content:flex-start;">

            <button type="button"
                    class="pg-btn pg-prev"
                    @if (!$bookings->onFirstPage()) onclick="window.location='{{ $bookings->previousPageUrl() }}'" @else disabled @endif>
                &larr;
            </button>

            @php
                $start = max(1, $bookings->currentPage() - 2);
                $end = min($bookings->lastPage(), $bookings->currentPage() + 2);
            @endphp

            @if ($start > 1)
                <button type="button" class="pg-btn pg-num" onclick="window.location='{{ $bookings->url(1) }}'">1</button>
                @if ($start > 2)
                    <span style="color:var(--muted);font-size:12px;padding:0 2px;">&hellip;</span>
                @endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                <button type="button"
                        class="pg-btn pg-num {{ $page === $bookings->currentPage() ? 'active' : '' }}"
                        onclick="window.location='{{ $bookings->url($page) }}'">
                    {{ $page }}
                </button>
            @endfor

            @if ($end < $bookings->lastPage())
                @if ($end < $bookings->lastPage() - 1)
                    <span style="color:var(--muted);font-size:12px;padding:0 2px;">&hellip;</span>
                @endif
                <button type="button" class="pg-btn pg-num" onclick="window.location='{{ $bookings->url($bookings->lastPage()) }}'">
                    {{ $bookings->lastPage() }}
                </button>
            @endif

            <button type="button"
                    class="pg-btn pg-next"
                    @if ($bookings->hasMorePages()) onclick="window.location='{{ $bookings->nextPageUrl() }}'" @else disabled @endif>
                &rarr;
            </button>

        </div>
    @endif
    </div>

    {{-- REJECT REASON POPUP --}}
    <div id="rejectReasonOverlay" aria-hidden="true">
        <div class="rr-card" role="dialog" aria-modal="true" aria-labelledby="rrTitle">
            <div class="rr-icon">!</div>
            <h3 id="rrTitle">Booking Rejected</h3>
            <p class="rr-sub"><span id="rrPackage"></span> &middot; <span id="rrDate"></span></p>

            <div class="rr-box">
                <div class="rr-label">Reason</div>
                <div class="rr-text" id="rrReason"></div>
            </div>

            <div class="rr-box" id="rrNoteBox" style="display:none;">
                <div class="rr-label">Note from admin</div>
                <div class="rr-text rr-note" id="rrNote"></div>
            </div>

            <p class="rr-help">
You may submit a new booking. Please ensure that your payment and booking details are accurate.            </p>

            <div class="rr-actions">
                <button type="button" class="u-btn ghost" id="rrClose" style="padding:10px;font-size:12px;font-weight:600;">Close</button>
                <a href="{{ route('user.booking') }}" class="u-btn" style="padding:10px;font-size:12px;font-weight:600;">Book Again</a>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    (function () {
        var overlay = document.getElementById('rejectReasonOverlay');
        if (!overlay) return;

        function openReject(btn) {
            var reason = (btn.dataset.reason || '').trim();
            var note = (btn.dataset.note || '').trim();

            document.getElementById('rrPackage').textContent = btn.dataset.package || '';
            document.getElementById('rrDate').textContent = btn.dataset.date || '';
            document.getElementById('rrReason').textContent = reason || 'Walang rason na nailagay ang admin.';

            var noteBox = document.getElementById('rrNoteBox');
            document.getElementById('rrNote').textContent = note;
            noteBox.style.display = note ? '' : 'none';

            overlay.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeReject() {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.js-show-reject').forEach(function (btn) {
            btn.addEventListener('click', function () { openReject(btn); });
        });

        document.getElementById('rrClose').addEventListener('click', closeReject);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeReject(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) closeReject();
        });
    })();
</script>
@endpush

@if (isset($stats) && $stats['total_visits'] > 0)
    @push('scripts')
    <script>
        document.querySelectorAll('.mb-tab').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.mb-tab').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                document.querySelectorAll('.mb-panel').forEach(function (p) { p.hidden = true; });
                document.getElementById(btn.dataset.target).hidden = false;
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('historyChart'), {
            type: 'bar',
            data: {
                labels: @json($stats['history_labels']),
                datasets: [{
                    label: 'Bookings',
                    data: @json($stats['history_values']),
                    backgroundColor: '#4b6bff',
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });

        var categoryLabels = @json($stats['category_labels']);
        var categoryValues = @json($stats['category_values']);
        var categoryColors = ['#4b6bff', '#ff6b8a', '#ffb648'];

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryValues,
                    backgroundColor: categoryColors,
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                cutout: '62%',
            },
        });

        var legendEl = document.getElementById('categoryLegend');
        var categoryTotal = categoryValues.reduce(function (a, b) { return a + b; }, 0);
        categoryLabels.forEach(function (label, i) {
            var pct = categoryTotal ? Math.round((categoryValues[i] / categoryTotal) * 100) : 0;
            var row = document.createElement('div');
            row.style.cssText = 'display:flex;align-items:center;gap:6px;';
            row.innerHTML =
                '<span style="width:10px;height:10px;border-radius:50%;background:' + categoryColors[i] + ';flex-shrink:0;"></span>' +
                '<span>' + label + '</span>' +
                '<b>' + categoryValues[i] + '</b>' +
                '<span style="color:var(--muted);font-size:11.5px;">(' + pct + '%)</span>';
            legendEl.appendChild(row);
        });
    </script>
    @endpush
@endif