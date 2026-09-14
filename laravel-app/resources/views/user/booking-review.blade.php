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

    <!-- Payment Confirmation Modal -->
    <div class="booking-modal-overlay" id="paymentModalOverlay">
        <div class="booking-modal">
            <div class="booking-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                    <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
                </svg>
            </div>
            <h3 id="paymentModalTitle">Confirm Payment</h3>
            <p class="booking-modal-sub" id="paymentModalSub">Are you sure you want to proceed with this payment method?</p>

            <div class="booking-modal-summary">
                <div class="booking-modal-row">
                    <span>Package</span>
                    <b>{{ $booking['package'] }}</b>
                </div>
                <div class="booking-modal-row">
                    <span>Payment Method</span>
                    <b id="confirmPaymentMethodText">—</b>
                </div>
            </div>

            <div class="booking-modal-actions">
                <button type="button" class="btn-cancel" id="paymentModalCancel">Cancel</button>
                <button type="button" class="btn-confirm" id="paymentModalConfirm">Yes, Confirm</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    #paymentSubmitBtn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        filter: grayscale(60%);
        pointer-events: none;
    }

    /* ── confirmation modal + loading state (shared pattern with booking.blade.php) ── */
    .booking-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .55);
        z-index: 200;
        align-items: center;
        justify-content: center;
    }
    .booking-modal-overlay.active { display: flex; }
    .booking-modal {
        background: #fff;
        border-radius: 16px;
        padding: 30px 26px;
        width: 90%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0,0,0,.25);
        animation: bookingModalPop .18s ease;
    }
    @keyframes bookingModalPop {
        from { transform: scale(.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .booking-modal-icon {
        width: 52px;
        height: 52px;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: var(--pink-pale, #ffe6ee);
        color: var(--pink-deep, #b82850);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .booking-modal h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #1a1523;
    }
    .booking-modal-sub {
        font-size: .85rem;
        color: #635c72;
        margin-bottom: 18px;
    }
    .booking-modal-summary {
        background: #f8f6f9;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 20px;
        text-align: left;
    }
    .booking-modal-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: .82rem;
        padding: 5px 0;
    }
    .booking-modal-row span { color: #9c94ab; }
    .booking-modal-row b { color: #1a1523; text-align: right; }
    .booking-modal-actions { display: flex; gap: 10px; }
    .booking-modal-actions button {
        flex: 1;
        padding: 11px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .86rem;
        border: none;
        cursor: pointer;
    }
    .btn-cancel { background: #f1eef2; color: #635c72; }
    .btn-cancel:hover { background: #e5e0e8; }
    .btn-confirm { background: var(--pink-deep, #b82850); color: #fff; }
    .btn-confirm:hover { background: var(--pink-dark, #d63e63); }

    .is-loading {
        opacity: .75;
        cursor: not-allowed;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-spinner {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, .4);
        border-top-color: #fff;
        display: inline-block;
        animation: btnSpin .7s linear infinite;
    }
    @keyframes btnSpin {
        to { transform: rotate(360deg); }
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
<script>
// ── Confirmation modal + loading state on submit (same pattern as booking.blade.php) ──
(function () {
    var form = document.getElementById('paymentForm');
    var submitBtn = document.getElementById('paymentSubmitBtn');
    var overlay = document.getElementById('paymentModalOverlay');
    var confirmBtn = document.getElementById('paymentModalConfirm');
    var cancelBtn = document.getElementById('paymentModalCancel');
    var confirmPaymentMethodText = document.getElementById('confirmPaymentMethodText');
    var paymentModalSub = document.getElementById('paymentModalSub');
    if (!form || !submitBtn || !overlay) return;

    var isConfirmed = false;

    function methodLabel(method) {
        if (method === 'qrph') return 'QR Ph';
        if (method === 'cash') return 'Cash on-site';
        return '—';
    }

    function openModal(method) {
        confirmPaymentMethodText.textContent = methodLabel(method);
        paymentModalSub.textContent = method === 'qrph'
            ? 'Your uploaded receipt will be sent for verification.'
            : 'Your slot stays reserved as "Pending payment" until you pay at the counter.';
        overlay.classList.add('active');
    }

    function closeModal() {
        overlay.classList.remove('active');
    }

    form.addEventListener('submit', function (e) {
        // Second time around — already confirmed, let it through.
        if (isConfirmed) {
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }
            var loadingLabel = document.querySelector('input[name="payment_method"]:checked')?.value === 'qrph'
                ? 'Sending Receipt...'
                : 'Confirming...';
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            submitBtn.innerHTML = '<span class="btn-spinner"></span> ' + loadingLabel;

            setTimeout(function () {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                }
            }, 15000);
            return;
        }

        // First time — validate, then show the confirmation modal instead of submitting directly.
        e.preventDefault();

        var checked = document.querySelector('input[name="payment_method"]:checked');
        if (!checked) {
            alert('Please select a payment method first.');
            return;
        }

        if (checked.value === 'qrph') {
            var receiptInput = document.getElementById('receiptInput');
            if (!receiptInput || !receiptInput.files || !receiptInput.files.length) {
                alert('Please upload your payment receipt first.');
                return;
            }
        }

        openModal(checked.value);
    });

    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    confirmBtn.addEventListener('click', function () {
        isConfirmed = true;
        closeModal();
        if (form.requestSubmit) {
            form.requestSubmit(submitBtn);
        } else {
            form.submit();
        }
    });
})();
</script>
@endpush
