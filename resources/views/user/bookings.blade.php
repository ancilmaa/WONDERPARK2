{{--
    resources/views/user/bookings.blade.php
    Route: GET  /user/bookings                        -> user.bookings
           POST /user/bookings/{booking}/cancel        -> user.bookings.cancel
           POST /user/bookings/{booking}/reschedule    -> user.bookings.reschedule
    Controller: App\Http\Controllers\User\BookingController@index / cancel / reschedule

    Expects from the controller:
      $bookings -> collection/array of
        ['id', 'category', 'package', 'date', 'pax', 'status_label', 'status_class']
        status_class is one of: green | amber | rose
--}}
@extends('layouts.user')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')
@section('page-subtitle', 'Manage your reservations')
@section('body-class', 'page-uniform')

@section('content')

    <div class="u-card">
        <h4>My Bookings</h4>
        <p>Manage upcoming &amp; past visits</p>
    </div>

    <div class="u-card-grid">
    @forelse ($bookings as $booking)
        <div class="booking-item">
            <div class="pkg">
                <div>
                    <span style="font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:2px;">{{ $booking['category'] }}</span>
                    <b>{{ $booking['package'] }}</b>
                    <span>{{ $booking['date'] }} · {{ $booking['pax'] }} pax</span>
                </div>
                            <span class="tag {{ $booking['status_class'] }}">{{ $booking['status_label'] }}</span>
            </div>
            @if ($booking['status_class'] === 'green' && !empty($booking['voucher_code']))
                <div class="u-card" style="margin:0 0 12px;padding:10px 12px;background:#f6f6f8;">
                    <span style="font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);">Voucher Code</span>
                    <div style="font-family:monospace;font-size:16px;font-weight:700;letter-spacing:1px;">{{ $booking['voucher_code'] }}</div>
                </div>
            @endif
            @if (!in_array($booking['status'], ['done', 'cancelled']))
                <div style="display:flex;gap:8px;margin:-4px 0 12px;">
                    <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}" class="u-btn ghost" style="flex:1;padding:8px;font-size:11.5px;text-align:center;">Reschedule</a>
                    @if ($booking['status_class'] !== 'green')
                        <form method="POST" action="{{ route('user.bookings.cancel', $booking['id']) }}" style="flex:1;" onsubmit="return confirm('Cancel this booking?');">
                            @csrf
                            <button type="submit" class="u-btn ghost" style="padding:8px;font-size:11.5px;color:#e24b4a;border-color:#e24b4a;">Cancel</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="u-card">
            <p>You don't have any bookings yet.</p>
        </div>
    @endforelse
    </div>

    @php
        $pendingBooking = collect($bookings)->firstWhere('status_class', 'amber');
    @endphp

    @if ($pendingBooking)
        <a href="{{ route('user.bookings.review', $pendingBooking['id']) }}" class="u-btn" style="margin-top:14px;">
            Review Booking
        </a>
    @endif

    <a href="{{ route('user.booking') }}" class="u-btn ghost" style="margin-top:10px;">+ New Booking</a>

@endsection