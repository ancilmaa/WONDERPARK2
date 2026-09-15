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

@section('content')

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
        <div class="u-card" style="margin-top:20px;display:flex;gap:0;text-align:center;padding:16px 8px;">
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
    @endif

    <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;">
        @forelse ($bookings as $booking)
            <div class="mb-row u-card" style="margin-top:0;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px;padding:18px 20px;">
                <div style="flex:1;min-width:220px;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:2px;">
                        <span style="font-size:10.5px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);">{{ $booking['category'] }}</span>
                        <span class="tag {{ $booking['status_class'] }}" style="white-space:nowrap;">{{ $booking['status_label'] }}</span>
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
                    @if ($booking['status'] === 'confirmed')
                        {{-- Already paid & confirmed: no more "pending payment" actions, just let them reschedule --}}
                        <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}"
                           class="u-btn ghost"
                           style="width:100%;padding:10px;font-size:12px;font-weight:600;text-align:center;">
                            Reschedule
                        </a>
                    @elseif ($booking['status_class'] === 'amber')
                        <div style="display:flex;flex-direction:column;gap:8px;">

                            <a href="{{ route('user.bookings.review', $booking['id']) }}"
                               class="u-btn"
                               style="width:100%;padding:10px;font-size:12px;font-weight:600;text-align:center;">
                                Review Booking
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

@endsection

@if (isset($stats) && $stats['total_visits'] > 0)
    @push('scripts')
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