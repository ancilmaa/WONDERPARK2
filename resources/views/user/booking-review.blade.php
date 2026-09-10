{{--
    resources/views/user/booking-review.blade.php
    Route: GET  /user/bookings/{booking}/review              -> user.bookings.review
           POST /user/bookings/{booking}/payment              -> user.bookings.payment
    Controller: App\Http\Controllers\User\BookingController@review / confirmPayment

    Expects from the controller:
      $booking -> ['id', 'package', 'date', 'pax', 'status_label', 'status_class']
--}}
@extends('layouts.user')

@section('title', 'Review Booking')
@section('page-title', 'Review Booking')
@section('page-subtitle', 'Confirm the details below before payment')
@section('body-class', 'page-uniform')

@section('content')

    <div class="u-card">
        <h4>{{ $booking['package'] }}</h4>
        <p>{{ $booking['date'] }} · {{ $booking['pax'] }} pax</p>
    </div>

    <div class="pkg">
        <div>
            <b>Status</b>
            <span>Current booking status</span>
        </div>
        <span class="tag {{ $booking['status_class'] }}">{{ $booking['status_label'] }}</span>
    </div>

    <form method="POST" action="{{ route('user.bookings.payment', $booking['id']) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="payment_method" value="qrph">

        <div class="u-card" style="margin-top:14px;">
            <h4>Payment method</h4>
            <p>Pay online via QR Ph to confirm your booking.</p>
        </div>

        <div class="pkg">
            <div>
                <b>QR Ph</b>
                <span>Scan with any GCash, Maya, or bank app</span>
            </div>
        </div>

        <div id="qrphPanel" class="u-card" style="margin-top:14px;text-align:center;">
            <h4 style="text-align:left;">Scan to pay</h4>
            <p style="text-align:left;">Open your GCash, Maya, or your bank's app, then scan this QR Ph code.</p>
            <img src="{{ asset('images/qrph.png') }}" alt="QR Ph code" style="max-width:220px;width:100%;margin:12px auto 4px;display:block;border-radius:12px;">
            <p style="text-align:left;font-size:11.5px;color:var(--muted);">After paying, upload a screenshot or receipt of your payment below, then tap "Submit Payment". Our staff will verify it before your booking is confirmed.</p>
        </div>

        <div class="u-card" style="margin-top:14px;">
            <h4>Proof of payment</h4>
            <p>Upload a screenshot or receipt (JPG, PNG, WEBP, or PDF — max 5MB).</p>
            <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required style="margin-top:10px;width:100%;">
            @error('payment_proof')
                <p style="color:#e24b4a;font-size:11.5px;margin-top:6px;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="u-btn" style="margin-top:14px;">
            <span id="submitLabel">Submit Payment</span>
        </button>
    </form>

    <a href="{{ route('user.bookings') }}" class="u-btn ghost" style="margin-top:10px;">Back to My Bookings</a>

@endsection