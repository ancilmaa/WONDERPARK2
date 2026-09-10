<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
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
        ]);

        return redirect()
            ->route('reservations.index')
            ->with('success', 'Reservation created successfully.');
    }

    public function show(Booking $reservation)
    {
        //
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