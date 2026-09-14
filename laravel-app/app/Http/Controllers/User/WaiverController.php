<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// use App\Models\Waiver;

class WaiverController extends Controller
{
    /**
     * Full waiver text, keyed by service (same keys used in
     * BookingController::$services). Whichever service the customer booked
     * (stored in session by BookingController@store) determines which
     * waiver copy is shown here. Move this to the database once waivers
     * need to be editable by admins.
     */
    protected array $waivers = [
        'dino_adventure' => [
            'park_name' => 'Dino Adventure',
            'intro' => 'In consideration for being permitted to enter REKS Amusement Facilities, (hereinafter referred to as the "Park"), the undersigned, on behalf of themselves and any minors under their supervision, hereby acknowledges and agrees as follows:',
            'sections' => [
                ['title' => '1. Assumption of Risks', 'body' => 'The undersigned acknowledges and understands that the activities and attractions at the Park involve inherent risks, including but not limited to, the risk of injury or harm, and hereby assumes all such risks.'],
                ['title' => '2. Release and Waiver', 'body' => 'The undersigned, on behalf of themselves and any minors under their supervision, hereby releases, waives, discharges, and covenants not to sue the Park, its owners, employees, agents, and representatives (hereinafter collectively referred to as the "Released Parties") from any and all liability, claims, demands, actions, and causes of action whatsoever arising out of or related to any loss, damage, or injury, including death, that may be sustained by the undersigned or any minor under their supervision while in or upon the Park premises.'],
                ['title' => '3. Responsibility for Minors', 'body' => 'The undersigned acknowledges and agrees that they are responsible for the supervision and actions of any minors under their care or supervision while in or upon the Park premises.'],
                ['title' => '4. Lost, Stolen, or Damaged Belongings', 'body' => 'The undersigned acknowledges and agrees that the Park shall not be responsible for any lost, stolen, or damaged belongings while in or upon the Park premises.'],
                ['title' => '5. Damages to Park Property', 'body' => 'The undersigned agrees to be held financially responsible for any damages caused by themselves or any minors under their supervision to any equipment or facility within the Park, based on the value of the equipment or facility and the extent of the damages incurred.'],
                ['title' => '6. False Information', 'body' => 'The undersigned acknowledges that providing false information on this agreement, including but not limited to, false identification or contact information, may result in legal action being taken against them to the fullest extent permitted by Philippine law.'],
                ['title' => '7. Full Extent of Philippine Law', 'body' => 'The undersigned agrees that this agreement shall be enforced to the fullest extent permitted by Philippine law.'],
            ],
        ],

        'rollerfever' => [
            'park_name' => 'Roller Fever',
            'intro' => 'In consideration for being permitted to enter Roller Fever, (hereinafter referred to as the "Park"), the undersigned, on behalf of themselves and any minors under their supervision, hereby acknowledges and agrees as follows:',
            'sections' => [
                ['title' => '1. Assumption of Risks', 'body' => 'The undersigned acknowledges and understands that the activities and attractions at the Park involve inherent risks, including but not limited to, the risk of injury or harm, and hereby assumes all such risks.'],
                ['title' => '2. Release and Waiver', 'body' => 'The undersigned, on behalf of themselves and any minors under their supervision, hereby releases, waives, discharges, and covenants not to sue the Park, its owners, employees, agents, and representatives (hereinafter collectively referred to as the "Released Parties") from any and all liability, claims, demands, actions, and causes of action whatsoever arising out of or related to any loss, damage, or injury, including death, that may be sustained by the undersigned or any minor under their supervision while in or upon the Park premises.'],
                ['title' => '3. Responsibility for Minors', 'body' => 'The undersigned acknowledges and agrees that they are responsible for the supervision and actions of any minors under their care or supervision while in or upon the Park premises.'],
                ['title' => '4. Lost, Stolen, or Damaged Belongings', 'body' => 'The undersigned acknowledges and agrees that the Park shall not be responsible for any lost, stolen, or damaged belongings while in or upon the Park premises.'],
                ['title' => '5. Damages to Park Property', 'body' => 'The undersigned agrees to be held financially responsible for any damages caused by themselves or any minors under their supervision to any equipment or facility within the Park, based on the value of the equipment or facility and the extent of the damages incurred.'],
                ['title' => '6. Full Extent of Philippine Law', 'body' => 'The undersigned agrees that this agreement shall be enforced to the fullest extent permitted by Philippine law.'],
                ['title' => '7. Data Privacy Consent', 'body' => 'The undersigned consents to the collection, processing, and storage of their personal information by Rollerfever for safety, security, operational, customer service, and legal purposes, in accordance with the Philippine Data Privacy Act of 2012 (RA 10173).'],
            ],
        ],
    ];

    /**
     * Show the waiver for the customer to review and sign.
     *
     * GET /user/waiver
     */
    public function show()
    {
        // Which service this booking was for (set in BookingController@store).
        // Defaults to Dino Adventure if, for whatever reason, nothing was
        // stored in session (e.g. the customer jumped straight to /user/waiver).
        $service = session('booking_service', 'dino_adventure');

        $waiver = $this->waivers[$service] ?? $this->waivers['dino_adventure'];

        return view('user.waiver', compact('waiver'));
    }

    /**
     * Store the customer's e-signature and proceed to booking.
     *
     * POST /user/waiver
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agree' => ['required', 'accepted'],
        ]);

        // TODO: persist the signed waiver once the Waiver model/table exists, e.g.:
        //
        // Waiver::create([
        //     'user_id'         => Auth::id(),
        //     'signature_name'  => $validated['signature_name'],
        //     'signed_at'       => now(),
        //     'ip_address'      => $request->ip(),
        // ]);

        // Waiver is the last step of the booking flow (Booking -> Payment -> Waiver),
        // so signing it finishes the process.
        return redirect()
            ->route('user.bookings')
            ->with('success', 'Waiver signed. Your booking is now confirmed!');
    }
}