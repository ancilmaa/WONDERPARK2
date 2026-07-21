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

    <div class="u-card" style="margin-top:20px;padding:0;overflow:hidden;font-size:14px;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:640px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(0,0,0,0.08);">
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Package</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Date</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Pax</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Status</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($bookings as $booking)
                    <tr style="{{ !$loop->last ? 'border-bottom:1px solid rgba(0,0,0,0.06);' : '' }}">
                        <td style="padding:20px;vertical-align:middle;font-size:14px;font-weight:600;">
                            {{ $booking['package'] }}
                        </td>
                        <td style="padding:20px;vertical-align:middle;white-space:nowrap;font-size:13px;color:var(--muted);">
                            {{ $booking['date'] }}
                        </td>
                        <td style="padding:20px;vertical-align:middle;white-space:nowrap;font-size:13px;color:var(--muted);">
                            {{ $booking['pax'] }} pax
                        </td>
                        <td style="padding:20px;vertical-align:middle;">
                            <span class="tag {{ $booking['status_class'] }}">{{ $booking['status_label'] }}</span>
                        </td>
                        <td style="padding:20px;vertical-align:middle;">
                            @if ($booking['status_class'] !== 'green')
                                <div style="display:flex;flex-direction:column;gap:8px;min-width:200px;">

                                    @if ($booking['status_class'] === 'amber')
                                        <a href="{{ route('user.bookings.review', $booking['id']) }}"
                                           class="u-btn"
                                           style="width:100%;padding:8px 8px;font-size:11px;font-weight:600;text-align:center;white-space:nowrap;">
                                            Review Booking
                                        </a>
                                    @endif

                                    <div style="display:flex;gap:8px;">
                                        <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}"
                                           class="u-btn ghost"
                                           style="flex:1;padding:8px 6px;font-size:11px;font-weight:600;text-align:center;white-space:nowrap;">
                                            Reschedule
                                        </a>

                                        <form method="POST"
                                              action="{{ route('user.bookings.cancel', $booking['id']) }}"
                                              style="flex:1;"
                                              onsubmit="return confirm('Cancel this booking?');">
                                            @csrf
                                            <button type="submit"
                                                    class="u-btn ghost"
                                                    style="width:100%;padding:8px 6px;font-size:11px;font-weight:600;color:#e24b4a;border-color:#e24b4a;white-space:nowrap;">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @else
                                <span style="font-size:13px;color:var(--muted);">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:24px 20px;text-align:center;font-size:13px;color:var(--muted);">
                            You don't have any bookings yet.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
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