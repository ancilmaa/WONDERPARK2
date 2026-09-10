{{--
    resources/views/user/payment-processing.blade.php
    Route: GET /app/payment/gcash/success/{booking} -> user.bookings.paymongo.success
    Controller: App\Http\Controllers\User\PaymongoPaymentController@success

    Shown right after the customer returns from PayMongo's hosted GCash
    page. The booking is confirmed in the background by the PayMongo
    webhook, not by this page — this is just a friendly waiting screen.
--}}
@extends('layouts.user')

@section('title', 'Confirming Payment')
@section('page-title', 'Confirming Payment')
@section('page-subtitle', 'Please wait a moment')
@section('body-class', 'page-uniform')

@section('content')

    <div class="u-card" style="text-align:center;padding:32px 20px;">
        <h4>Payment received!</h4>
        <p style="margin-top:8px;">We're confirming your GCash payment with PayMongo. This usually takes just a few seconds — you'll be redirected automatically.</p>
    </div>

    <a href="{{ route('user.bookings') }}" class="u-btn" style="margin-top:14px;">Go to My Bookings</a>

    <script>
        setTimeout(function () {
            window.location.href = "{{ route('user.bookings') }}";
        }, 4000);
    </script>

@endsection
