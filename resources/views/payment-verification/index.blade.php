{{--
    resources/views/payment-verification/index.blade.php
    Route: GET  /payment-verification                          -> payment-verification
           POST /payment-verification/{booking}/approve         -> payment-verification.approve
           POST /payment-verification/{booking}/reject          -> payment-verification.reject
    Controller: App\Http\Controllers\PaymentVerificationController

    Expects from the controller:
      $pending -> bookings currently in 'awaiting_verification', oldest first
      $recent  -> last 15 bookings that were approved/rejected
--}}
@extends('layouts.sidebar')

@section('title', 'Payment Verification')

@section('styles')
<style>
    .pv-toolbar{
        background:var(--card); padding:22px 26px; margin-bottom:20px;
        border-radius:16px; box-shadow:var(--shadow-sm);
    }
    .pv-toolbar .eyebrow{ font-size:.68rem; font-weight:700; color:var(--pink-deep); text-transform:uppercase; letter-spacing:.09em; margin-bottom:6px; }
    .pv-toolbar h2{ font-size:1.4rem; font-weight:700; color:var(--ink); }
    .pv-toolbar p{ font-size:.85rem; color:var(--muted); margin-top:4px; }

    .pv-grid{ display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px; margin-bottom:28px; }

    .pv-card{ background:var(--card); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .pv-card-body{ padding:18px 20px; }
    .pv-card .category{ font-size:.66rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--pink-deep); }
    .pv-card .package{ font-size:1rem; font-weight:700; color:var(--ink); margin-top:2px; }
    .pv-card .meta{ font-size:.78rem; color:var(--muted); margin-top:3px; }
    .pv-card .price{ font-size:1.05rem; font-weight:800; color:var(--ink); margin-top:10px; }
    .pv-card .customer{ margin-top:10px; padding-top:10px; border-top:1px solid var(--line); font-size:.8rem; color:var(--ink-soft); }
    .pv-card .customer b{ color:var(--ink); }
    .pv-card .submitted{ font-size:.72rem; color:var(--muted); margin-top:6px; }

    .pv-proof{ display:block; background:var(--bg); }
    .pv-proof img{ width:100%; max-height:220px; object-fit:contain; display:block; }
    .pv-proof .pdf-badge{ display:flex; align-items:center; justify-content:center; gap:8px; height:120px; color:var(--ink-soft); font-weight:600; font-size:.85rem; }

    .pv-actions{ display:flex; gap:8px; padding:14px 20px; border-top:1px solid var(--line); }
    .pv-btn{ flex:1; padding:9px; border-radius:10px; border:1px solid var(--line-strong); font-weight:700; font-size:.8rem; cursor:pointer; font-family:'Inter',sans-serif; text-align:center; }
    .pv-btn.approve{ background:var(--green); border-color:var(--green); color:#fff; }
    .pv-btn.approve:hover{ opacity:.9; }
    .pv-btn.reject{ background:#fff; border-color:var(--deduct); color:var(--deduct); }
    .pv-btn.reject:hover{ background:var(--deduct-soft); }

    .pv-empty{ background:var(--card); border-radius:16px; box-shadow:var(--shadow-sm); padding:40px 20px; text-align:center; color:var(--muted); }
    .pv-empty i{ font-size:1.8rem; margin-bottom:10px; opacity:.5; }

    .pv-table{ background:var(--card); border-radius:16px; box-shadow:var(--shadow-sm); overflow:hidden; }
    .pv-table table{ width:100%; border-collapse:collapse; font-size:.8rem; }
    .pv-table th{ text-align:left; padding:12px 16px; font-size:.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); border-bottom:1px solid var(--line); }
    .pv-table td{ padding:12px 16px; border-bottom:1px solid var(--line); color:var(--ink-soft); }
    .pv-table tr:last-child td{ border-bottom:none; }
    .pv-tag{ display:inline-block; padding:3px 10px; border-radius:999px; font-size:.68rem; font-weight:700; }
    .pv-tag.confirmed{ background:var(--green-light); color:var(--green); }
    .pv-tag.rejected{ background:var(--deduct-soft); color:var(--deduct); }

    .section-heading{ font-size:.95rem; font-weight:700; color:var(--ink); margin:0 0 14px; }

    .u-alert{ padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:.85rem; font-weight:600; }
    .u-alert.success{ background:var(--green-light); color:var(--green); }
    .u-alert.error{ background:var(--deduct-soft); color:var(--deduct); }
</style>
@endsection

@section('content')

    @if (session('success'))
        <div class="u-alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="u-alert error">{{ session('error') }}</div>
    @endif

    <div class="pv-toolbar">
        <div class="eyebrow">Confirm before payout</div>
        <h2>Payment Verification</h2>
        <p>Review the uploaded proof of payment for each booking. A booking only becomes Confirmed once you approve it here — customers cannot confirm their own payment.</p>
    </div>

    <h3 class="section-heading">Awaiting review ({{ count($pending) }})</h3>

    @if (count($pending) === 0)
        <div class="pv-empty">
            <i class="fa-solid fa-circle-check"></i>
            <p>Nothing to review right now. New submissions will show up here.</p>
        </div>
    @else
        <div class="pv-grid">
        @foreach ($pending as $b)
            <div class="pv-card">
                @if ($b['proof_url'])
                    <a href="{{ $b['proof_url'] }}" target="_blank" class="pv-proof">
                        @if ($b['proof_is_pdf'])
                            <div class="pdf-badge"><i class="fa-solid fa-file-pdf"></i> View uploaded PDF receipt</div>
                        @else
                            <img src="{{ $b['proof_url'] }}" alt="Proof of payment for booking #{{ $b['id'] }}">
                        @endif
                    </a>
                @endif

                <div class="pv-card-body">
                    <div class="category">{{ $b['category'] }}</div>
                    <div class="package">{{ $b['package'] }}</div>
                    <div class="meta">{{ $b['visit_date'] }} · {{ $b['tier'] }} pax</div>
                    <div class="price">₱{{ number_format($b['price'], 2) }}</div>

                    <div class="customer">
                        <b>{{ $b['customer_name'] }}</b><br>
                        {{ $b['customer_email'] }}
                    </div>
                    <div class="submitted">Submitted {{ $b['submitted_at'] }} · Booking #{{ $b['id'] }}</div>
                </div>

                <div class="pv-actions">
                    <form method="POST" action="{{ route('payment-verification.approve', $b['id']) }}" style="flex:1;" onsubmit="return confirm('Confirm this booking as paid?');">
                        @csrf
                        <button type="submit" class="pv-btn approve" style="width:100%;">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('payment-verification.reject', $b['id']) }}" style="flex:1;" onsubmit="return promptRejectReason(event, this);">
                        @csrf
                        <input type="hidden" name="reason" value="">
                        <button type="submit" class="pv-btn reject" style="width:100%;">Reject</button>
                    </form>
                </div>
            </div>
        @endforeach
        </div>
    @endif

    @if (count($recent) > 0)
        <h3 class="section-heading">Recently reviewed</h3>
        <div class="pv-table">
            <table>
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Package</th>
                        <th>Outcome</th>
                        <th>Reviewed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent as $b)
                        <tr>
                            <td>#{{ $b['id'] }}</td>
                            <td>{{ $b['customer_name'] }}</td>
                            <td>{{ $b['category'] }} — {{ $b['package'] }}</td>
                            <td>
                                @if ($b['status'] === 'confirmed')
                                    <span class="pv-tag confirmed">Confirmed</span>
                                @else
                                    <span class="pv-tag rejected">Rejected{{ $b['rejection_reason'] ? ' — '.$b['rejection_reason'] : '' }}</span>
                                @endif
                            </td>
                            <td>{{ $b['verified_at'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection

@push('scripts')
<script>
    function promptRejectReason(event, form) {
        const reason = prompt('Reason for rejecting this payment (optional):', '');
        if (reason === null) {
            event.preventDefault();
            return false;
        }
        form.querySelector('input[name="reason"]').value = reason;
        return true;
    }
</script>
@endpush
