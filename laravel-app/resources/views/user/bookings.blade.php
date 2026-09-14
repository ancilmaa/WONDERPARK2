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
                                              id="cancelForm-{{ $booking['id'] }}"
                                              class="cancel-form">
                                            @csrf
                                            <button type="button"
                                                    class="u-btn ghost cancel-trigger"
                                                    data-form-id="cancelForm-{{ $booking['id'] }}"
                                                    data-package="{{ $booking['package'] }}"
                                                    data-date="{{ $booking['date'] }}"
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

    <!-- Cancel Confirmation Modal -->
    <div class="booking-modal-overlay" id="cancelModalOverlay">
        <div class="booking-modal">
            <div class="booking-modal-icon" style="background:#ffe6e6;color:#e24b4a;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 8v4"></path><path d="M12 16h.01"></path>
                </svg>
            </div>
            <h3>Cancel This Booking?</h3>
            <p class="booking-modal-sub">This action cannot be undone.</p>

            <div class="booking-modal-summary">
                <div class="booking-modal-row">
                    <span>Package</span>
                    <b id="confirmCancelPackageText">—</b>
                </div>
                <div class="booking-modal-row">
                    <span>Date</span>
                    <b id="confirmCancelDateText">—</b>
                </div>
            </div>

            <div class="booking-modal-actions">
                <button type="button" class="btn-cancel" id="cancelModalDismiss">Keep Booking</button>
                <button type="button" class="btn-confirm danger" id="cancelModalConfirm">Yes, Cancel</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
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
    .btn-confirm.danger { background: #e24b4a; }
    .btn-confirm.danger:hover { background: #c93f3e; }

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
// ── Cancel confirmation modal + loading state (same pattern as booking.blade.php) ──
(function () {
    var overlay = document.getElementById('cancelModalOverlay');
    var confirmBtn = document.getElementById('cancelModalConfirm');
    var dismissBtn = document.getElementById('cancelModalDismiss');
    var packageText = document.getElementById('confirmCancelPackageText');
    var dateText = document.getElementById('confirmCancelDateText');
    if (!overlay || !confirmBtn) return;

    var activeForm = null;

    function openModal(trigger) {
        activeForm = document.getElementById(trigger.dataset.formId);
        packageText.textContent = trigger.dataset.package || '—';
        dateText.textContent = trigger.dataset.date || '—';
        overlay.classList.add('active');
    }

    function closeModal() {
        overlay.classList.remove('active');
        activeForm = null;
    }

    document.querySelectorAll('.cancel-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            openModal(trigger);
        });
    });

    dismissBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    confirmBtn.addEventListener('click', function () {
        if (!activeForm) return;

        confirmBtn.disabled = true;
        confirmBtn.classList.add('is-loading');
        confirmBtn.innerHTML = '<span class="btn-spinner"></span> Cancelling...';

        if (activeForm.requestSubmit) {
            activeForm.requestSubmit();
        } else {
            activeForm.submit();
        }
    });
})();
</script>
@endpush
