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

    {{-- Booking summary --}}
    <div class="u-card">
        <h4>{{ $booking['package'] }}</h4>
        <p>{{ $booking['date'] }} &middot; {{ $booking['pax'] }} pax</p>
    </div>

    <div class="pkg" style="margin-top:10px;">
        <div>
            <b>Status</b>
            <span>Current booking status</span>
        </div>
        <span class="tag {{ $booking['status_class'] }}">{{ $booking['status_label'] }}</span>
    </div>

    <form method="POST" action="{{ route('user.bookings.payment', $booking['id']) }}" enctype="multipart/form-data" id="paymentForm">
        @csrf

        {{-- Payment method selection --}}
        <div class="u-card" style="margin-top:20px;">
            <h4>Payment method</h4>
            <p>Choose how you'd like to pay for this booking.</p>
        </div>

        <div class="payment-options" style="display:flex;flex-direction:column;gap:10px;margin-top:10px;">

            <label class="pkg payment-option" data-method="qrph" style="cursor:pointer;align-items:center;transition:box-shadow .15s ease, border-color .15s ease;">
                <div>
                    <b>QR Ph</b>
                    <span>Scan with any GCash, Maya, or bank app</span>
                </div>
                <div class="price">
                    <input type="radio" name="payment_method" value="qrph" onchange="togglePaymentPanels(this.value)" style="width:18px;height:18px;">
                </div>
            </label>

            <label class="pkg payment-option" data-method="cash" style="cursor:pointer;align-items:center;transition:box-shadow .15s ease, border-color .15s ease;">
                <div>
                    <b>Cash on-site</b>
                    <span>Pay at the counter upon arrival</span>
                </div>
                <div class="price">
                    <input type="radio" name="payment_method" value="cash" onchange="togglePaymentPanels(this.value)" style="width:18px;height:18px;">
                </div>
            </label>

        </div>

        {{-- QR Ph instructions --}}
        <div id="qrphPanel" class="u-card" style="margin-top:14px;display:none;">
            <h4>Scan to pay</h4>
            <p>Open your GCash, Maya, or your bank's app, then scan this QR Ph code.</p>

            <div style="text-align:center;margin:14px 0 4px;">
                <img src="{{ asset('images/qrph.png') }}" alt="QR Ph code" style="max-width:220px;width:100%;border-radius:12px;">
            </div>

            <p style="font-size:11.5px;color:var(--muted);margin-top:8px;">
                After paying, upload a screenshot or photo of your payment receipt below, then tap "Send Receipt". We'll verify and confirm your booking shortly.
            </p>

            <div style="margin-top:10px;display:flex;flex-direction:column;gap:6px;">
                <label for="receiptInput" style="font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;">Payment receipt</label>
                <input type="file" name="receipt" id="receiptInput" accept="image/*">
                <span id="receiptFileName" style="font-size:11.5px;color:var(--muted);"></span>
            </div>
        </div>

        {{-- Cash instructions --}}
        <div id="cashPanel" class="u-card" style="margin-top:14px;display:none;">
            <h4>Pay at the counter</h4>
            <p>Please settle payment upon arrival at WonderPark Amusement Com Inc. &mdash; Lipa Branch. Your slot will remain reserved as <b>"Pending payment"</b> until then.</p>
        </div>

        <button type="submit" class="u-btn" id="paymentSubmitBtn" style="margin-top:18px;width:100%;" disabled>
            <span id="submitLabel">Select a payment method</span>
        </button>
    </form>

    <a href="{{ route('user.bookings') }}" class="u-btn ghost" style="margin-top:10px;display:block;text-align:center;">
        Back to My Bookings
    </a>

@endsection

@push('styles')
<style>
    #paymentSubmitBtn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        filter: grayscale(60%);
        pointer-events: none;
    }
</style>
@endpush

@push('scripts')
<script>
    function togglePaymentPanels(method) {
        document.getElementById('qrphPanel').style.display = method === 'qrph' ? 'block' : 'none';
        document.getElementById('cashPanel').style.display = method === 'cash' ? 'block' : 'none';

        document.querySelectorAll('.payment-option').forEach(function (el) {
            const isSelected = el.dataset.method === method;
            el.style.borderColor = isSelected ? 'currentColor' : '';
            el.style.boxShadow = isSelected ? '0 0 0 1px currentColor' : 'none';
        });

        updateSubmitState(method);
    }

    function updateSubmitState(method) {
        const btn = document.getElementById('paymentSubmitBtn');
        const label = document.getElementById('submitLabel');
        const receiptInput = document.getElementById('receiptInput');

        if (method === 'cash') {
            label.textContent = 'Confirm Cash Payment';
            btn.disabled = false;
        } else if (method === 'qrph') {
            label.textContent = 'Send Receipt';
            btn.disabled = !(receiptInput && receiptInput.files && receiptInput.files.length > 0);
        } else {
            label.textContent = 'Select a payment method';
            btn.disabled = true;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const checked = document.querySelector('input[name="payment_method"]:checked');
        if (checked) {
            togglePaymentPanels(checked.value);
        } else {
            updateSubmitState(null);
        }

        const receiptInput = document.getElementById('receiptInput');
        receiptInput?.addEventListener('change', function () {
            const nameEl = document.getElementById('receiptFileName');
            nameEl.textContent = this.files && this.files.length ? this.files[0].name : '';
            const checkedNow = document.querySelector('input[name="payment_method"]:checked');
            updateSubmitState(checkedNow ? checkedNow.value : null);
        });
    });
</script>
@endpush