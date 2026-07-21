<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaiverController extends Controller
{
    /**
     * The full liability + data privacy waiver text, shared across all
     * services (Dino Adventure, RollerFever, Field of Rides). Move this
     * to the database (e.g. an App\Models\Waiver model) once it needs
     * to be editable by admins.
     */
    protected function waiverData(): array
    {
        return [
            'park_name' => 'WonderPark Amusement',
            'intro' => 'By checking the box below and submitting this form, I ("Guest" or "Guardian") confirm that I am at least 18 years old, or if under 18, that a parent/legal guardian has reviewed and consented to this waiver on my behalf, and that I voluntarily agree to the terms below for all activities at the park, including Dino Adventure, RollerFever, and Field of Rides.',
            'sections' => [
                [
                    'title' => '1. Assumption of Risk',
                    'body'  => 'I understand that amusement rides, skating, softplay, and related activities carry inherent risks, including but not limited to falls, collisions, muscle strain, and other injuries, and I voluntarily assume all such risks for myself and any minors under my supervision.',
                ],
                [
                    'title' => '2. Dino Adventure (Softplay & Kids Party)',
                    'body'  => 'Children must be accompanied by a paying or registered guardian at all times inside the play area. Guests must follow posted age, height, and weight guidelines per equipment. Socks may be required. Management is not liable for injuries resulting from horseplay, disregard of posted rules, or pre-existing medical conditions not disclosed prior to entry.',
                ],
                [
                    'title' => '3. RollerFever (Skate Rink)',
                    'body'  => 'Skating carries a risk of falls and collisions. Guests must wear the provided or rented skates and safety gear as instructed by staff and must skate in the direction and manner indicated by rink personnel. Management is not liable for injuries resulting from improper use of equipment or failure to follow rink safety instructions.',
                ],
                [
                    'title' => '4. Field of Rides (Amusement Rides & Games)',
                    'body'  => 'Minimum height, weight, or health requirements apply per ride and are posted at each attraction. Guests under the posted height requirement must be accompanied by a paying guardian. Guests with heart conditions, motion sickness, recent surgery, pregnancy, or similar health concerns should not ride and must inform staff before boarding. Management reserves the right to refuse or stop service for any guest who does not comply with posted safety guidelines.',
                ],
                [
                    'title' => '5. Media & Photo Release',
                    'body'  => 'I consent to being photographed or filmed during park activities and grant WonderPark Amusement permission to use such images for promotional purposes, unless I notify staff in writing prior to my visit.',
                ],
                [
                    'title' => '6. Data Privacy Consent',
                    'body'  => 'Personal information collected during booking and waiver signing (name, contact details, and related booking information) will be kept confidential and will not be disclosed, sold, or shared with unauthorized third parties. It may, however, be shared with third parties strictly where necessary — such as payment processors for transaction verification, insurance providers in case of an incident, or government authorities where required by law — solely for the purpose of facilitating the booking, ensuring safety, or complying with legal obligations, in accordance with the Data Privacy Act of 2012 (RA 10173).',
                ],
                [
                    'title' => '7. Medical Emergency Consent',
                    'body'  => 'In the event of an accident or medical emergency, I authorize park staff to administer basic first aid and, if necessary, arrange for emergency medical transport, with costs to be borne by the guest or their guardian.',
                ],
                [
                    'title' => '8. Release & Indemnification',
                    'body'  => 'I release WonderPark Amusement, its owners, staff, and representatives from any liability for injury, loss, or damage arising from participation in park activities, except in cases of gross negligence or willful misconduct, and I agree to indemnify the park against claims arising from my own or my minor\'s violation of posted rules.',
                ],
            ],
        ];
    }

    /**
     * Show the waiver page.
     *
     * GET /user/waiver  ->  user.waiver
     */
    public function show()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        // Already signed? Skip straight to the dashboard.
        if (session('waiver_signed')) {
            return redirect('/home');
        }

        $waiver = $this->waiverData();

        return view('user.waiver', compact('waiver'));
    }

    /**
     * Record acceptance of the waiver and continue to the dashboard.
     *
     * POST /user/waiver  ->  user.waiver.store
     */
    public function store(Request $request): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'agree' => ['required', 'accepted'],
        ]);

        DB::table('users')
            ->where('user_id', session('user_id'))
            ->update([
                'waiver_signed_at' => now(),
                'updated_at'       => now(),
            ]);

        session(['waiver_signed' => true]);

        return redirect('/home')->with('success', 'Waiver accepted. Welcome!');
    }
}