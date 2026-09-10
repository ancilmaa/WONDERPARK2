<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Staff-facing review queue for customer-submitted proof of payment.
 *
 * This is the other half of the customer-facing upload flow in
 * App\Http\Controllers\User\BookingController@confirmPayment. A booking
 * only ever becomes 'confirmed' through the approve() action here — the
 * customer cannot self-confirm their own payment.
 */
class PaymentVerificationController extends Controller
{
    /**
     * Human-readable service names, kept in sync with the small $services
     * map in User\BookingController. Not duplicating the much larger
     * $packages price list here — the raw package slug + stored price is
     * enough for a staff member to verify a payment.
     */
    private const SERVICE_NAMES = [
        'dino_adventure' => 'Dino Adventure',
        'rollerfever'    => 'RollerFever',
        'field_of_rides' => 'Field of Rides',
    ];

    /**
     * Queue of bookings awaiting staff review, plus a short history of
     * recently verified/rejected ones for context.
     */
    public function index(): View|RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $pending = Booking::with('user')
            ->where('status', 'awaiting_verification')
            ->orderBy('payment_submitted_at')
            ->get()
            ->map(fn (Booking $b) => $this->present($b));

        $recent = Booking::with('user')
            ->whereIn('status', ['confirmed', 'pending_payment'])
            ->whereNotNull('payment_verified_at')
            ->orderByDesc('payment_verified_at')
            ->limit(15)
            ->get()
            ->map(fn (Booking $b) => $this->present($b));

        return view('payment-verification.index', compact('pending', 'recent'));
    }

    /**
     * Shape a Booking model into the display array expected by the
     * payment-verification view.
     */
    private function present(Booking $booking): array
    {
        return [
            'id'              => $booking->id,
            'customer_name'   => $booking->user->name ?? 'Unknown customer',
            'customer_email'  => $booking->user->email ?? '',
            'category'        => self::SERVICE_NAMES[$booking->service] ?? ucfirst(str_replace('_', ' ', $booking->service)),
            'package'         => ucwords(str_replace('_', ' ', $booking->package)),
            'tier'            => $booking->tier,
            'price'           => $booking->price,
            'visit_date'      => optional($booking->visit_date)->format('M j, Y'),
            'submitted_at'    => optional($booking->payment_submitted_at)->format('M j, Y g:i A'),
            'verified_at'     => optional($booking->payment_verified_at)->format('M j, Y g:i A'),
            'status'          => $booking->status,
            'rejection_reason'=> $booking->payment_rejection_reason,
            'proof_url'       => $booking->payment_proof_path ? Storage::disk('public')->url($booking->payment_proof_path) : null,
            'proof_is_pdf'    => $booking->payment_proof_path ? str_ends_with(strtolower($booking->payment_proof_path), '.pdf') : false,
        ];
    }

    /**
     * Approve a submitted payment — this is the only place a booking is
     * allowed to move into 'confirmed' status.
     */
    public function approve(int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('status', 'awaiting_verification')->find($booking);

        abort_if(!$bookingModel, 404);

        $bookingModel->update([
            'status'              => 'confirmed',
            'payment_verified_at' => now(),
        ]);

        return redirect()
            ->route('payment-verification')
            ->with('success', "Booking #{$bookingModel->id} confirmed.");
    }

    /**
     * Reject a submitted payment (e.g. proof is unclear, wrong amount,
     * duplicate reference number). The booking goes back to
     * 'pending_payment' so the customer can resubmit; the old proof file
     * is discarded.
     */
    public function reject(Request $request, int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $bookingModel = Booking::where('status', 'awaiting_verification')->find($booking);

        abort_if(!$bookingModel, 404);

        if ($bookingModel->payment_proof_path) {
            Storage::disk('public')->delete($bookingModel->payment_proof_path);
        }

        $bookingModel->update([
            'status'                    => 'pending_payment',
            'payment_proof_path'        => null,
            'payment_verified_at'       => now(),
            'payment_rejection_reason'  => $validated['reason'] ?? null,
        ]);

        return redirect()
            ->route('payment-verification')
            ->with('success', "Booking #{$bookingModel->id} sent back for resubmission.");
    }
}
