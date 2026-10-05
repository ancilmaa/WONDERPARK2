<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Support\BookingCatalog;
use App\Support\BookingPricingOverrides;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    /**
     * Allowed rejection reasons (must match the options in the reject modal).
     */
    private const REJECT_REASONS = [
        'Invalid or unclear payment receipt',
        'Incorrect payment amount',
        'Selected date/time is fully booked',
        'Selected package is not available',
        'Incomplete or incorrect booking details',
        'Others',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bookings = Booking::with(['customer', 'user'])->latest()->get();

        return view('customer.reservations', compact('bookings'));
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created reservation from the "New Reservation" modal.
     * Accepts either an existing customer_id, or guest customer_name/contact
     * (matches the fallback fields shown in the blade when no customers exist yet).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'       => ['nullable', 'exists:customers,id'],
            'customer_name'     => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'customer_contact'  => ['nullable', 'string', 'max:50'],
            'package'           => ['required', 'string', 'max:255'],
            'pax'               => ['required', 'integer', 'min:1'],
            'reservation_date'  => ['required', 'date'],
            'reservation_time'  => ['required'],
            'notes'             => ['nullable', 'string'],
        ]);

        Booking::create([
            'customer_id'      => $validated['customer_id'] ?? null,
            'customer_name'    => $validated['customer_name'] ?? null,
            'customer_contact' => $validated['customer_contact'] ?? null,
            'package'          => $validated['package'],
            'pax'              => $validated['pax'],
            'reservation_date' => $validated['reservation_date'],
            'reservation_time' => $validated['reservation_time'],
            'notes'            => $validated['notes'] ?? null,
            'status'           => 'pending',
            'approval_status'  => 'pending',
        ]);

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation created successfully.');
    }

    /**
     * Return a single reservation's details as JSON.
     * Used by the expandable "Recent Visitor Logins" row on the
     * Visitor Summary page (fetch('/reservations/{id}')).
     */
    public function show(Booking $reservation)
    {
        return response()->json([
            'package'          => $reservation->package,
            'pax'              => $reservation->pax,
            'reservation_date' => optional($reservation->reservation_date)->format('M d, Y'),
            'reservation_time' => $reservation->reservation_time,
            'status'           => $reservation->status,
            'approval_status'  => $reservation->approval_status,
            'reject_reason'    => $reservation->reject_reason,
            'reject_note'      => $reservation->reject_note,
        ]);
    }

    public function edit(Booking $reservation)
    {
        //
    }

    /**
     * Used for quick status updates (confirm/paid/cancel) from the admin table.
     * The blade posts here via the inline status dropdown on each row.
     */
    public function update(Request $request, Booking $reservation)
    {
        // Quick status-only update (from the inline dropdown)
        if ($request->has('status') && !$request->has('package')) {
            $validated = $request->validate([
                'status' => ['required', 'in:pending,confirmed,paid,cancelled'],
            ]);

            $reservation->update($validated);

            return redirect()->route('reservations.index')->with('success', 'Reservation updated.');
        }

        // Full edit from the Edit modal
        $validated = $request->validate([
            'customer_name'     => ['required', 'string', 'max:255'],
            'customer_contact'  => ['nullable', 'string', 'max:50'],
            'package'           => ['required', 'string', 'max:255'],
            'pax'               => ['required', 'integer', 'min:1'],
            'reservation_date'  => ['required', 'date'],
            'reservation_time'  => ['required'],
            'notes'             => ['nullable', 'string'],
        ]);

        $reservation->update($validated);

        return redirect()->route('reservations.index')->with('success', 'Reservation updated successfully.');
    }

    /**
     * Approve / reject a reservation from the admin table.
     *
     * PATCH /reservations/{reservation}/status
     * Approve body: status=approved
     * Reject body:  status=rejected, reject_reason, reject_note
     *               (reject_note required when reject_reason is "Others").
     */
    public function updateStatus(Request $request, Booking $reservation)
    {
        $validated = $request->validate([
            'status'        => ['required', Rule::in(['pending', 'confirmed', 'paid', 'cancelled', 'approved', 'rejected'])],
            'reject_reason' => [
                'required_if:status,rejected',
                'nullable',
                'string',
                Rule::in(self::REJECT_REASONS),
            ],
            'reject_note'   => [
                'required_if:reject_reason,Others',
                'nullable',
                'string',
                'max:1000',
            ],
        ], [
            'reject_reason.required_if' => 'Please select a reason for rejecting this reservation.',
            'reject_note.required_if'   => 'Please add a note when the reason is "Others".',
        ]);

        // REJECT
        if ($validated['status'] === 'rejected') {
            $reservation->update([
                'status'          => 'rejected',
                'approval_status' => 'rejected',
                'reject_reason'   => $validated['reject_reason'],
                'reject_note'     => $validated['reject_note'] ?? null,
            ]);

            return redirect()
                ->route('reservations.index')
                ->with('success', 'Reservation rejected.');
        }

        // APPROVE
        if ($validated['status'] === 'approved') {
            $reservation->update([
                'status'              => 'confirmed',
                'approval_status'     => 'approved',
                'payment_verified_at' => $reservation->payment_verified_at ?? now(),
                'reject_reason'       => null,
                'reject_note'         => null,
            ]);

            return redirect()
                ->route('reservations.index')
                ->with('success', 'Reservation approved.');
        }

        // OTHER STATUSES
        $reservation->update([
            'status'        => $validated['status'],
            'reject_reason' => null,
            'reject_note'   => null,
        ]);

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation status updated.');
    }

    /**
     * Approve a customer's proof of payment: the booking becomes
     * 'confirmed' (customer can no longer reschedule, and the voucher can
     * now be redeemed at the POS).
     *
     * POST /reservations/{reservation}/approve-payment
     */
    public function approvePayment(Booking $reservation)
    {
        if ($reservation->status !== 'awaiting_verification') {
            return redirect()->route('reservations.index')
                ->with('error', 'Only bookings waiting for approval can be approved.');
        }

        $reservation->update([
            'status'                   => 'confirmed',
            'payment_verified_at'      => now(),
            'payment_rejection_reason' => null,
        ]);

        $emailed = $this->emailPaymentResult($reservation, true);

        return redirect()->route('reservations.index')->with(
            'success',
            'Payment approved. Booking confirmed.' . ($emailed ? ' The customer was notified by email.' : '')
        );
    }

    /**
     * Reject a customer's proof of payment. The booking goes back to
     * 'pending_payment' with the reason, so the customer sees why and can
     * upload a correct proof (or reschedule / cancel).
     *
     * POST /reservations/{reservation}/reject-payment
     */
    public function rejectPayment(Request $request, Booking $reservation)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        if ($reservation->status !== 'awaiting_verification') {
            return redirect()->route('reservations.index')
                ->with('error', 'Only bookings waiting for approval can be rejected.');
        }

        $reservation->update([
            'status'                   => 'pending_payment',
            'payment_rejection_reason' => $validated['reason'],
            'payment_verified_at'      => null,
        ]);

        $emailed = $this->emailPaymentResult($reservation, false);

        return redirect()->route('reservations.index')->with(
            'success',
            'Payment rejected. The customer was asked to upload a new proof.' . ($emailed ? ' They were notified by email.' : '')
        );
    }

    /**
     * Email the customer the result of the payment check (approved or
     * rejected). Same approach as the 2FA / password emails: plain text via
     * the app's configured mailer, wrapped in try/catch so a mail problem
     * never blocks the admin's approve/reject action. Returns true when the
     * email was handed to the mailer, false if there was no email address or
     * sending failed.
     */
    private function emailPaymentResult(Booking $booking, bool $approved): bool
    {
        $customer = $booking->display_customer;
        $email    = $customer->email ?? null;

        if (!$email) {
            return false; // walk-in / guest reservation without an email
        }

        $services = BookingCatalog::services();
        $packages = BookingPricingOverrides::applyToPackages(BookingCatalog::basePackages());

        $name        = $customer->fullname ?? 'there';
        $serviceName = $services[$booking->service]['name'] ?? ucwords(str_replace('_', ' ', (string) $booking->service));
        $packageName = $packages[$booking->service][$booking->package]['name'] ?? $booking->package;
        $date        = optional($booking->display_date)->format('F j, Y');
        $time        = $booking->display_time;
        $pax         = $booking->display_pax;
        $myBookings  = route('user.bookings');

        $details = "Attraction: {$serviceName}\n"
                 . "Package: {$packageName}\n"
                 . "Date: {$date}" . ($time ? " at {$time}" : '') . "\n"
                 . "Guests: {$pax}\n";

        if ($approved) {
            $subject = 'Your WonderPark booking is confirmed';
            $body = "Hi {$name},\n\n"
                  . "Good news! We checked your proof of payment and your booking is now confirmed.\n\n"
                  . $details
                  . "Voucher code: {$booking->voucher_code}\n\n"
                  . "Show this voucher code to the cashier when you arrive.\n"
                  . "Please note that confirmed bookings can no longer be rescheduled.\n\n"
                  . "You can view your booking here: {$myBookings}\n\n"
                  . "See you at WonderPark!";
        } else {
            $subject = 'Action needed: your WonderPark payment proof was not accepted';
            $body = "Hi {$name},\n\n"
                  . "We could not accept the proof of payment you sent for this booking.\n\n"
                  . $details
                  . "Reason: {$booking->payment_rejection_reason}\n\n"
                  . "Please open My Bookings, tap \"Upload New Proof\", and send a clear screenshot or photo of your payment. "
                  . "You can still reschedule or cancel this booking while it is not yet approved.\n\n"
                  . "My Bookings: {$myBookings}\n\n"
                  . "Thank you,\nWonderPark";
        }

        try {
            Mail::raw($body, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error('Payment result email failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete a reservation (used by the trash-icon button + confirm modal).
     */
    public function destroy(Booking $reservation)
    {
        $reservation->delete();

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation deleted.');
    }
}