<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

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
     * Approve or reject a reservation (Status column on the admin table).
     *
     * - approved: the customer can now see their reservation/voucher.
     * - rejected: a reason is required and is shown to the customer.
     */
    public function updateStatus(Request $request, Booking $reservation)
    {
        // Once the cashier has used the voucher, the status is final ("Done").
        if (!empty($reservation->voucher_used_at)) {
            return redirect()
                ->route('reservations.index')
                ->with('error', 'This reservation is already completed.');
        }

        $validated = $request->validate([
            'status'        => ['required', 'in:approved,rejected'],
            'reject_reason' => ['required_if:status,rejected', 'nullable', 'string', 'in:' . implode(',', self::REJECT_REASONS)],
            'reject_note'   => ['nullable', 'string', 'max:500'],
        ]);

        $rejected = $validated['status'] === 'rejected';

        $reservation->update([
            'approval_status' => $validated['status'],
            'reject_reason'   => $rejected ? $validated['reject_reason'] : null,
            'reject_note'     => $rejected && $validated['reject_reason'] === 'Others'
                ? ($validated['reject_note'] ?? null)
                : null,
        ]);

        return redirect()
            ->route('reservations.index')
            ->with('success', $rejected ? 'Reservation rejected.' : 'Reservation approved.');
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