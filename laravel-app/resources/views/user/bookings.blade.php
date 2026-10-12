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
--}}
@extends('layouts.user')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')
@section('page-subtitle', 'Manage your reservations')
@section('body-class', 'page-uniform')

@push('styles')
<style>
/* My Bookings — same look as the booking wizard's voucher ticket */
.mt-wrap{ --mt-surface:#fff; --mt-soft:#FBF8FB; --mt-text:var(--ink); --mt-sub:var(--ink-soft); --mt-line:var(--line); }
[data-theme="dark"] .mt-wrap{ --mt-surface:var(--d-surface); --mt-soft:var(--d-surface-soft); --mt-text:var(--d-text); --mt-sub:var(--d-text-soft); --mt-line:var(--d-border); }
.mt-hero{ margin-top:14px; display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; padding:22px 24px; border-radius:24px; color:#fff;
  background:linear-gradient(135deg,var(--pink-deep),#F2762E); box-shadow:var(--shadow-pop); }
.mt-hero small{ opacity:.88; font-weight:800; letter-spacing:.1em; font-size:11px; }
.mt-hero h2{ margin:4px 0 2px; font-size:26px; font-weight:800; letter-spacing:-.01em; color:#fff; }
.mt-hero p{ margin:0; font-size:13.5px; opacity:.92; color:#fff; }
.mt-btn{ border:none; cursor:pointer; border-radius:14px; padding:11px 18px; font-weight:800; font-size:13px; font-family:inherit; text-decoration:none; display:inline-block; text-align:center;
  white-space:nowrap; transition:all .18s var(--ease); }
.mt-btn:hover{ transform:translateY(-2px); }
.mt-btn.go{ background:linear-gradient(135deg,var(--pink-deep),#F2762E); color:#fff; box-shadow:0 10px 20px -10px rgba(184,40,80,.7); }
.mt-btn.white{ background:#fff; color:var(--pink-deep); box-shadow:0 10px 20px -12px rgba(0,0,0,.5); }
.mt-btn.ghost{ background:var(--mt-soft); color:var(--mt-text); border:2px solid var(--mt-line); }
.mt-btn.ghost:hover{ border-color:var(--pink-deep); color:var(--pink-deep); }
.mt-btn.danger{ background:transparent; color:var(--rose-deep); border:2px solid var(--rose-deep); }
.mt-search{ margin-top:20px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.mt-search input{ flex:1; min-width:220px; padding:13px 18px; font-size:13.5px; font-family:inherit; border-radius:999px; border:2px solid var(--mt-line); background:var(--mt-surface); color:var(--mt-text); outline:none; transition:border-color .18s; }
.mt-search input:focus{ border-color:var(--pink-deep); box-shadow:0 0 0 3px var(--pink-light); }
.mt-meta{ margin:10px 4px 0; font-size:12.5px; color:var(--mt-sub); }
.mt-list{ margin-top:16px; display:flex; flex-direction:column; gap:18px; }
.mt-ticket{ position:relative; background:var(--mt-surface); color:var(--mt-text); border-radius:24px; border:1px solid var(--mt-line); box-shadow:var(--shadow-card); overflow:hidden; transition:transform .25s var(--ease), box-shadow .25s var(--ease); }
.mt-ticket:hover{ transform:translateY(-3px); box-shadow:var(--shadow-pop); }
.mt-top{ padding:18px 22px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; background:linear-gradient(135deg,var(--pink-deep),#F2762E); color:#fff; }
.mt-top small{ opacity:.9; font-weight:800; letter-spacing:.08em; font-size:11px; text-transform:uppercase; }
.mt-top h3{ margin:4px 0 0; font-size:21px; color:#fff; }
.mt-when{ opacity:.92; font-size:13px; margin-top:4px; }
.mt-pill{ display:inline-flex; align-items:center; gap:8px; padding:7px 16px; border-radius:999px; font-weight:800; font-size:12.5px; background:var(--amber-pale); color:var(--amber-deep); }
.mt-pill::before{ content:""; width:9px; height:9px; border-radius:50%; background:currentColor; animation:mtPulse 1.4s ease-in-out infinite; }
.mt-pill.ok{ background:var(--teal-pale); color:var(--teal-deep); }
.mt-pill.ok::before{ animation:none; }
.mt-pill.bad{ background:var(--rose-pale); color:var(--rose-deep); }
@keyframes mtPulse{ 0%,100%{ opacity:1; transform:scale(1);} 50%{ opacity:.45; transform:scale(.7);} }
.mt-mid{ padding:14px 22px 16px; font-size:13.5px; }
.mt-row{ display:flex; justify-content:space-between; gap:12px; padding:4px 0; }
.mt-row small{ display:block; color:var(--mt-sub); font-size:11.5px; }
.mt-row.sum{ border-top:2px dashed var(--mt-line); margin-top:8px; padding-top:10px; font-size:17px; font-weight:800; }
.mt-row.sum span:last-child{ color:var(--pink-deep); }
.mt-perf{ position:relative; border-top:3px dotted var(--mt-line); margin:0 18px; }
.mt-perf::before,.mt-perf::after{ content:""; position:absolute; top:-15px; width:26px; height:26px; border-radius:50%; background:var(--bg); }
.mt-perf::before{ left:-31px; } .mt-perf::after{ right:-31px; }
[data-theme="dark"] .mt-perf::before,[data-theme="dark"] .mt-perf::after{ background:var(--d-bg); }
.mt-bot{ padding:16px 22px 20px; display:flex; gap:20px; justify-content:space-between; align-items:center; flex-wrap:wrap; }
.mt-voucher{ min-width:220px; }
.mt-vlabel{ font-size:11px; font-weight:800; letter-spacing:.12em; color:var(--mt-sub); }
.mt-vwrap{ position:relative; display:inline-flex; align-items:center; gap:10px; margin-top:6px; }
.mt-vcode{ font-family:'Courier New',monospace; font-size:22px; font-weight:800; letter-spacing:.18em; padding:8px 14px; border-radius:12px; background:var(--gold-pale); color:#6b3b00; user-select:all; }
[data-theme="dark"] .mt-vcode{ background:rgba(255,201,72,.12); color:#FFD98A; }
.mt-vcode.lock{ filter:blur(6px); user-select:none; pointer-events:none; }
.mt-lock{ position:absolute; left:0; top:6px; width:100%; height:calc(100% - 0px); display:grid; place-items:center; font-size:22px; pointer-events:none; }
.mt-copy{ border:none; cursor:pointer; border-radius:999px; padding:7px 14px; font-weight:800; font-size:12px; background:var(--teal-pale); color:var(--teal-deep); font-family:inherit; }
.mt-vnote{ display:block; margin-top:6px; font-size:11.5px; color:var(--mt-sub); }
.mt-line{ display:flex; align-items:flex-start; justify-content:center; margin:14px 0 2px; }
.mt-line div{ flex:1; text-align:center; font-size:11.5px; font-weight:700; color:var(--mt-sub); position:relative; }
.mt-line div::before{ content:""; display:block; width:16px; height:16px; margin:0 auto 6px; border-radius:50%; background:var(--mt-line); position:relative; z-index:1; }
.mt-line div::after{ content:""; position:absolute; top:7px; left:-50%; width:100%; height:3px; background:var(--mt-line); }
.mt-line div:first-child::after{ display:none; }
.mt-line div.on{ color:var(--mt-text); }
.mt-line div.on::before,.mt-line div.on::after{ background:var(--teal); }
.mt-line div.cur::before{ background:var(--gold); animation:mtPulse 1.4s ease-in-out infinite; }
.mt-actions{ display:flex; flex-direction:column; gap:8px; min-width:230px; flex:0 1 300px; }
.mt-actions p{ margin:0; font-size:12px; line-height:1.5; color:var(--mt-sub); }
.mt-actions p.err{ color:var(--rose-deep); }
.mt-actions .mt-pair{ display:flex; gap:8px; }
.mt-actions .mt-pair > *{ flex:1; }
.mt-actions .mt-pair form{ display:block; }
.mt-actions .mt-pair form .mt-btn{ width:100%; }
.mt-empty{ text-align:center; padding:34px 20px; border-radius:24px; border:2px dashed var(--mt-line); background:var(--mt-surface); color:var(--mt-sub); font-size:14px; }
.mt-empty b{ display:block; font-size:40px; margin-bottom:6px; }
.mt-wrap .pg-btn.active{ background:var(--pink-deep); border-color:var(--pink-deep); color:#fff; }
</style>
@endpush

@section('content')

    @if (isset($stats) && $stats['total_visits'] > 0)
        <div class="mb-tabs" id="mbTabs">
            <button type="button" class="mb-tab active" data-target="bookingsPanel">My Bookings</button>
            <button type="button" class="mb-tab" data-target="analyticsPanel">Analytics</button>
        </div>
    @endif

    <div class="mt-hero">
        <div>
            <small>&#127903;&#65039; MANAGE YOUR VISITS</small>
            <h2>My Bookings</h2>
            <p>Upcoming &amp; past visits</p>
        </div>
        <a href="{{ route('user.booking') }}" class="mt-btn white">+ New Booking &#127905;</a>
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

    <div id="bookingsPanel" class="mb-panel mt-wrap">

    {{-- Search my bookings --}}
    <form method="GET" action="{{ route('user.bookings') }}" role="search" class="mt-search">
        <input type="search" name="q" value="{{ $q ?? '' }}"
               placeholder="&#128269; Search bookings — package, date, status, WP-000027, voucher code…"
               aria-label="Search bookings" autocomplete="off">
        <button type="submit" class="mt-btn go">Search</button>
        @if (!empty($q))
            <a href="{{ route('user.bookings') }}" class="mt-btn ghost">Clear</a>
        @endif
    </form>
    @if (!empty($q))
        <p class="mt-meta">
            {{ $bookings->total() }} result{{ $bookings->total() === 1 ? '' : 's' }} for &ldquo;{{ $q }}&rdquo;
        </p>
    @endif

    <div class="mt-list">
        @forelse ($bookings as $booking)
            @php
                $approved = in_array($booking['status'], ['confirmed', 'done'], true);
                $inReview = $booking['status'] === 'awaiting_verification';
                $pending  = $booking['status'] === 'pending_payment';
                $rejected = $pending && !empty($booking['rejection_reason']);
                $pillCls  = $approved ? 'ok' : ($rejected ? 'bad' : '');
                $emoji    = ['dino_adventure' => '🦖', 'rollerfever' => '🛼', 'field_of_rides' => '🎡'][$booking['service']] ?? '🎟️';
                $guests   = $booking['pax'] . ' ' . ((int) $booking['pax'] === 1 ? 'guest' : 'guests');
                $hasCode  = $approved && !empty($booking['voucher_code']);
            @endphp
            <article class="mt-ticket">
                <div class="mt-top">
                    <div>
                        <small>{{ $emoji }} {{ $booking['category'] }}</small>
                        <h3>{{ $booking['reference'] }}</h3>
                        <div class="mt-when">📅 {{ $booking['date'] }}{{ $booking['time_label'] ? ' · ' . $booking['time_label'] : '' }} · {{ $guests }}</div>
                    </div>
                    <span class="mt-pill {{ $pillCls }}">{{ $booking['status_label'] }}</span>
                </div>

                <div class="mt-mid">
                    <div class="mt-row"><span>{{ $booking['package'] }}<small>{{ $guests }}</small></span></div>
                    <div class="mt-row sum"><span>Total</span><span>{{ $booking['price'] }}</span></div>
                    @if ($inReview || $approved)
                        <div class="mt-line">
                            <div class="on">Submitted</div>
                            <div class="{{ $approved ? 'on' : 'on cur' }}">In review</div>
                            <div class="{{ $approved ? 'on' : '' }}">Approved</div>
                        </div>
                    @endif
                </div>

                <div class="mt-perf"></div>

                <div class="mt-bot">
                    <div class="mt-voucher">
                        <div class="mt-vlabel">YOUR VOUCHER CODE</div>
                        @if ($hasCode)
                            <div class="mt-vwrap">
                                <span class="mt-vcode">{{ $booking['voucher_code'] }}</span>
                                <button type="button" class="mt-copy" data-code="{{ $booking['voucher_code'] }}">Copy</button>
                            </div>
                        @else
                            {{-- decorative placeholder only — the real code is never sent until approved --}}
                            <div class="mt-vwrap">
                                <span class="mt-vcode lock" aria-hidden="true">{{ strtoupper(\Illuminate\Support\Str::random(8)) }}</span>
                                <span class="mt-lock">🔒</span>
                            </div>
                            <span class="mt-vnote">Unlocks once your payment is approved</span>
                        @endif
                    </div>

                    <div class="mt-actions">
                        @if ($approved)
                            <p>
                                @if ($booking['status'] === 'confirmed')
                                    Approved &middot; rescheduling is no longer available
                                @else
                                    Voucher already used
                                @endif
                            </p>
                            <a href="{{ route('user.bookings.receipt', $booking['id']) }}" class="mt-btn ghost">View receipt</a>
                        @elseif ($inReview)
                            <p>We're checking your payment. You can still reschedule until it's approved.</p>
                            <div class="mt-pair">
                                <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}" class="mt-btn ghost">Reschedule</a>
                                <form method="POST" action="{{ route('user.bookings.cancel', $booking['id']) }}"
                                      onsubmit="return confirm('Cancel this booking?');">
                                    @csrf
                                    <button type="submit" class="mt-btn danger">Cancel</button>
                                </form>
                            </div>
                        @elseif ($pending)
                            @if ($rejected)
                                <p class="err">Your payment proof was not accepted: {{ $booking['rejection_reason'] }}. Please upload a new proof.</p>
                            @endif
                            <a href="{{ route('user.bookings.review', $booking['id']) }}" class="mt-btn go">
                                {{ $rejected ? 'Upload New Proof' : 'Review Booking' }}
                            </a>
                            <div class="mt-pair">
                                <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}" class="mt-btn ghost">Reschedule</a>
                                <form method="POST" action="{{ route('user.bookings.cancel', $booking['id']) }}"
                                      onsubmit="return confirm('Cancel this booking?');">
                                    @csrf
                                    <button type="submit" class="mt-btn danger">Cancel</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="mt-empty">
                @if (!empty($q))
                    <b>&#128269;</b>
                    No bookings match &ldquo;{{ $q }}&rdquo;. Try a package name, date or status.
                @else
                    <b>&#127905;</b>
                    You don't have any bookings yet.
                @endif
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

@endsection

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
                    backgroundColor: '#4F46E5',
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
        var categoryColors = ['#4F46E5', '#0D9488', '#F59E0B'];

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

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.mt-copy');
        if (!b) return;
        if (navigator.clipboard) navigator.clipboard.writeText(b.getAttribute('data-code'));
        b.textContent = 'Copied \u2713';
        setTimeout(function () { b.textContent = 'Copy'; }, 1600);
    });
</script>
@endpush
