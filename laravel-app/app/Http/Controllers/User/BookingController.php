<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

// use App\Models\Booking;

class BookingController extends Controller
{
    /**
     * The party experiences REKS Amusement offers. Move this to the
     * database (e.g. an App\Models\Service model) once it needs to be
     * editable by admins.
     */
    protected array $services = [
        'dino_adventure' => [
            'name'    => 'Dino Adventure',
            'tagline' => 'Kids Party & Softplay',
            'image'   => 'dino_1.jpg',
        ],
        'rollerfever' => [
            'name'    => 'RollerFever',
            'tagline' => 'Skate Rink Parties',
            'image'   => 'roller-fever.jpg',
        ],
        'field_of_rides' => [
            'name'    => 'Field of Rides',
            'tagline' => 'Amusement Rides & Games',
            'image'   => 'field-of-rides-icon.jpg',
        ],
    ];

    /**
     * Packages available for booking, grouped by service. Move this to the
     * database (e.g. an App\Models\Package model) once package management
     * needs to be editable by admins.
     *
     * Each package has several pax "tiers" with a total price — the
     * customer picks a service, then a package, then a tier (pax count).
     *
     * 'category' groups packages for the filter tabs on the booking page:
     *   'solo'     — single-guest / walk-in / per-head ticket pricing
     *   'bundle'   — group promo bundles (buy X get 1 free, etc.)
     *   'packages' — full party packages (exclusive/non-exclusive use)
     */
    protected array $packages = [
        'dino_adventure' => [
            'non_exclusive' => [
                'name'     => 'Non-Exclusive — Shared Play Area',
                'desc'     => 'Shared softplay & party area · 1-hr Dino mascot appearance included',
                'image'    => 'dino_1.jpg',
                'badge'    => 'Most affordable',
                'category' => 'packages',
                'tiers'    => [
                    10 => 15000,
                    20 => 28000,
                    30 => 39000,
                ],
            ],
            'exclusive_weekdays' => [
                'name'     => 'Exclusive — Private Party (Mon–Fri)',
                'desc'     => 'Private use of the whole play area, weekdays only · 1-hr Dino mascot appearance',
                'image'    => 'dino-venue.jpg',
                'badge'    => 'Weekdays only',
                'category' => 'packages',
                'tiers'    => [
                    40 => 62000,
                    50 => 75000,
                    60 => 88000,
                    70 => 97500,
                    80 => 110000,
                ],
            ],
            'exclusive_weekends' => [
                'name'     => 'Exclusive — Private Party (Sat, Sun & Holidays)',
                'desc'     => 'Private use of the whole play area, weekends & holidays · 1-hr Dino mascot appearance',
                'image'    => 'dino-venue.jpg',
                'badge'    => 'Weekends & holidays',
                'category' => 'packages',
                'tiers'    => [
                    40 => 67000,
                    50 => 80000,
                    60 => 93000,
                    70 => 102500,
                    80 => 115000,
                ],
            ],

            // --- Walk-in promo pricing, from the counter price list ---
            'walkin_1hour' => [
                'name'     => 'Walk-in — 1 Hour Play Pass',
                'desc'     => '1 hour of softplay access per guest (walk-in promo rate)',
                'image'    => 'dino_1.jpg',
                'badge'    => 'Walk-in promo',
                'category' => 'solo',
                'tiers'    => [
                    1  => 299,
                    2  => 598,
                    3  => 897,
                    4  => 1196,
                    5  => 1495,
                    6  => 1794,
                    7  => 2093,
                    8  => 2392,
                    9  => 2691,
                    10 => 2990,
                ],
            ],
            'walkin_2hour' => [
                'name'     => 'Walk-in — 2 Hour Play Pass',
                'desc'     => '2 hours of softplay access per guest (walk-in promo rate)',
                'image'    => 'dino_1.jpg',
                'badge'    => 'Walk-in promo',
                'category' => 'solo',
                'tiers'    => [
                    1  => 399,
                    2  => 798,
                    3  => 1197,
                    4  => 1596,
                    5  => 1995,
                    6  => 2394,
                    7  => 2793,
                    8  => 3192,
                    9  => 3591,
                    10 => 3990,
                ],
            ],
            'walkin_allday' => [
                'name'     => 'Walk-in — All Day Play Pass',
                'desc'     => 'Unlimited-time softplay access per guest for the day (walk-in promo rate)',
                'image'    => 'dino_1.jpg',
                'badge'    => 'Walk-in promo',
                'category' => 'solo',
                'tiers'    => [
                    1  => 599,
                    2  => 1198,
                    3  => 1797,
                    4  => 2396,
                    5  => 2995,
                    6  => 3594,
                    7  => 4193,
                    8  => 4792,
                    9  => 5391,
                    10 => 5990,
                ],
            ],
        ],

        'rollerfever' => [
            'weekday' => [
                'name'     => 'Weekday Rates',
                'desc'     => 'Skate rink party packages by number of guests (Mon–Fri)',
                'image'    => 'roller-fever.jpg',
                'badge'    => 'Weekdays',
                'category' => 'packages',
                'tiers'    => [
                    10 => 11994,
                    15 => 16200,
                    20 => 20400,
                    25 => 24390,
                    30 => 28800,
                    35 => 32550,
                    40 => 36000,
                    45 => 39150,
                ],
            ],
            'weekend' => [
                'name'     => 'Weekend Rates',
                'desc'     => 'Skate rink party packages by number of guests (Sat–Sun)',
                'image'    => 'roller-skates.jpg',
                'badge'    => 'Weekends',
                'category' => 'packages',
                'tiers'    => [
                    10 => 15600,
                    15 => 22500,
                    20 => 28800,
                    25 => 34500,
                    30 => 39600,
                    35 => 42000,
                    40 => 45600,
                    45 => 48600,
                ],
            ],

            // --- Walk-in promo pricing, from the counter price list ---
            'walkin_1hour' => [
                'name'     => 'Walk-in — 1 Hour Skate Pass',
                'desc'     => '1 hour of skating per guest (walk-in promo rate)',
                'image'    => 'roller-fever.jpg',
                'badge'    => 'Walk-in promo',
                'category' => 'solo',
                'tiers'    => [
                    1  => 249,
                    2  => 498,
                    3  => 747,
                    4  => 996,
                    5  => 1245,
                    6  => 1494,
                    7  => 1743,
                    8  => 1992,
                    9  => 2241,
                    10 => 2490,
                ],
            ],
            'walkin_2hour' => [
                'name'     => 'Walk-in — 2 Hour Skate Pass',
                'desc'     => '2 hours of skating per guest (walk-in promo rate)',
                'image'    => 'roller-fever.jpg',
                'badge'    => 'Walk-in promo',
                'category' => 'solo',
                'tiers'    => [
                    1  => 399,
                    2  => 798,
                    3  => 1197,
                    4  => 1596,
                    5  => 1995,
                    6  => 2394,
                    7  => 2793,
                    8  => 3192,
                    9  => 3591,
                    10 => 3990,
                ],
            ],
            'walkin_allday' => [
                'name'     => 'Walk-in — All Day Skate Pass',
                'desc'     => 'Unlimited-time skating per guest for the day (walk-in promo rate)',
                'image'    => 'roller-fever.jpg',
                'badge'    => 'Walk-in promo',
                'category' => 'solo',
                'tiers'    => [
                    1  => 599,
                    2  => 1198,
                    3  => 1797,
                    4  => 2396,
                    5  => 2995,
                    6  => 3594,
                    7  => 4193,
                    8  => 4792,
                    9  => 5391,
                    10 => 5990,
                ],
            ],
            'group_bundle_1hour' => [
                'name'     => 'Group Bundle — 1 Hour (4+1 Free)',
                'desc'     => 'Buy 4 guest passes, get 1 free — good for barkada or family groups (1 hour skating)',
                'image'    => 'roller-fever.jpg',
                'badge'    => 'Group promo',
                'category' => 'bundle',
                'tiers'    => [
                    5 => 996,
                ],
            ],
            'group_bundle_2hour' => [
                'name'     => 'Group Bundle — 2 Hours (4+1 Free)',
                'desc'     => 'Buy 4 guest passes, get 1 free — good for barkada or family groups (2 hours skating)',
                'image'    => 'roller-fever.jpg',
                'badge'    => 'Group promo',
                'category' => 'bundle',
                'tiers'    => [
                    5 => 1596,
                ],
            ],
        ],

        // --- Field of Rides: individual carnival-style ride tickets,
        // grouped by per-head price tier straight from the ticket booth
        // price board (₱60 / ₱120 / ₱150 rides), plus the all-access
        // "Try Every Ride" promo pass.
        'field_of_rides' => [
            'try_every_ride' => [
                'name'     => 'Try Every Ride — All Access Pass',
                'desc'     => 'One ride each on every attraction listed on the price board (Tiger Train, Mini Carousel, Star Speed, Little Chicken, Boat Pool, Carousel, Flying Chair, Mini Ferris Wheel, Samba Baloon, Crazy Plane, Vikings, Go-Kart)',
                'image'    => 'field-of-rides.jpg',
                'badge'    => 'Best value',
                'category' => 'solo',
                'tiers'    => [
                    1  => 600,
                    2  => 1200,
                    3  => 1800,
                    4  => 2400,
                    5  => 3000,
                    6  => 3600,
                    7  => 4200,
                    8  => 4800,
                    9  => 5400,
                    10 => 6000,
                ],
            ],
            'rides_60' => [
                'name'     => 'Rides — ₱60 Per Head',
                'desc'     => 'Tiger Train, Mini Carousel, Star Speed, Little Chicken, Boat Pool, Carousel, Flying Chair, Mini Ferris Wheel, Samba Baloon, Crazy Plane',
                'image'    => 'field-of-rides.jpg',
                'badge'    => 'Per ride',
                'category' => 'solo',
                'tiers'    => [
                    1  => 60,
                    2  => 120,
                    3  => 180,
                    4  => 240,
                    5  => 300,
                    6  => 360,
                    7  => 420,
                    8  => 480,
                    9  => 540,
                    10 => 600,
                ],
            ],
            'rides_120' => [
                'name'     => 'Rides — ₱120 Per Head',
                'desc'     => 'Vikings, Go-Kart',
                'image'    => 'field-of-rides.jpg',
                'badge'    => 'Per ride',
                'category' => 'solo',
                'tiers'    => [
                    1  => 120,
                    2  => 240,
                    3  => 360,
                    4  => 480,
                    5  => 600,
                    6  => 720,
                    7  => 840,
                    8  => 960,
                    9  => 1080,
                    10 => 1200,
                ],
            ],
            'rides_150' => [
                'name'     => 'Rides — ₱150 Per Head',
                'desc'     => 'Inflatable Playground (30 mins), Mini Trampoline (30 mins), Rev & Roll (per car), Happy Cars (per ride), Jurassic Adventure (per ride)',
                'image'    => 'field-of-rides.jpg',
                'badge'    => 'Per ride',
                'category' => 'solo',
                'tiers'    => [
                    1  => 150,
                    2  => 300,
                    3  => 450,
                    4  => 600,
                    5  => 750,
                    6  => 900,
                    7  => 1050,
                    8  => 1200,
                    9  => 1350,
                    10 => 1500,
                ],
            ],
        ],
    ];

    /**
     * Flat add-on fees per service (not tied to a specific package/tier).
     * NOTE: not yet wired into the booking form UI or store() pricing —
     * needs checkbox/quantity inputs in booking.blade.php first. Ping me
     * with that file's markup and we'll hook these in.
     */
    protected array $addons = [
        'dino_adventure' => [
            'guardian'          => ['name' => 'Guardian Entry',          'price' => 50],
            'additional_30mins' => ['name' => 'Additional 30 Minutes',   'price' => 149],
            'additional_hour'   => ['name' => 'Additional Hour',         'price' => 199],
        ],
        'rollerfever' => [
            'socks'        => ['name' => 'Socks (required for entry)',  'price' => 50],
            'skate_rental' => ['name' => 'Skates and Gears Rental',     'price' => 50],
        ],
    ];

    /**
     * Inclusions shared across every package, grouped by service.
     */
    protected array $inclusions = [
        'dino_adventure' => [
            '3 hrs unlimited play at softplay (max no. of package chosen)',
            '3 hrs exclusive use of party area',
            '1 set meal each (max no. of package chosen)',
            'Dino mascot dance & photo ops',
            'Party program with host',
            'Basic balloon set-up',
            'Basic sound system',
            'Tables & chairs (max no. of package chosen)',
            'Nametag & party games',
            'Digital themed invitation',
        ],
        'rollerfever' => [
            '2 hours skating',
            '3 hours use of party area',
            '1 set meal per pax',
            'Basic balloon set-up',
            'Basic sound system',
            'Tables & chairs',
            'Digital themed invitation',
        ],
        'field_of_rides' => [
            'One ride/round per ticket purchased',
            'Minimum height requirement applies per ride (posted at each attraction)',
            'Guests under 4ft must be accompanied by a paying guardian — no chaperone-only riders',
            'Riders must be free from motion sickness, heart conditions, or other health restrictions listed at the ticket booth',
            'Management reserves the right to refuse service to guests who do not follow safety guidelines',
        ],
    ];

    /**
     * Human-readable labels for the filter tabs on the booking page.
     */
    protected array $packageCategories = [
        'solo'     => 'Solo',
        'bundle'   => 'Bundle',
        'packages' => 'Packages',
    ];

    /**
     * Show the booking form (date + package selection).
     *
     * GET /user/booking
     */
    public function create()
    {
        $dates = $this->upcomingDates();
        $services = $this->services;
        $packages = $this->sortedPackages();
        $inclusions = $this->inclusions;
        $addons = $this->addons;
        $packageCategories = $this->packageCategories;

        return view('user.booking', compact('dates', 'services', 'packages', 'inclusions', 'addons', 'packageCategories'));
    }

    /**
     * Return $this->packages with each service's package list sorted
     * ascending by starting price (lowest tier price first), so the
     * cheapest option in every category always shows up first.
     */
    protected function sortedPackages(): array
    {
        $sorted = $this->packages;

        foreach ($sorted as $svcCode => $servicePackages) {
            uasort($servicePackages, function ($a, $b) {
                return min($a['tiers']) <=> min($b['tiers']);
            });
            $sorted[$svcCode] = $servicePackages;
        }

        return $sorted;
    }

    /**
     * Store a new booking request.
     *
     * POST /user/booking
     */
    public function store(Request $request): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'visit_date' => ['required', 'date'],
            'visit_time' => ['required', 'date_format:H:i'],
            'service'    => ['required', 'string', 'in:' . implode(',', array_keys($this->services))],
            'package'    => ['required', 'string'],
            'tier'       => ['required', 'integer'],
        ]);

        $servicePackages = $this->packages[$validated['service']] ?? [];

        if (!array_key_exists($validated['package'], $servicePackages)) {
            return back()
                ->withInput()
                ->withErrors(['package' => 'Please choose a valid package for this service.']);
        }

        $package = $servicePackages[$validated['package']];

        if (!array_key_exists($validated['tier'], $package['tiers'])) {
            return back()
                ->withInput()
                ->withErrors(['tier' => 'Please choose a valid pax tier for this package.']);
        }

        $booking = Booking::create([
            'user_id'    => session('user_id'),
            'service'    => $validated['service'],
            'package'    => $validated['package'],
            'tier'       => $validated['tier'],
            'price'      => $package['tiers'][$validated['tier']],
            'visit_date' => $validated['visit_date'],
            'visit_time' => $validated['visit_time'],
            'status'     => 'pending_payment',
        ]);

        // Remember which service (Dino Adventure vs RollerFever vs Field of
        // Rides) this booking is for, so the Waiver step later on can show
        // the correct waiver text.
        session(['booking_service' => $validated['service']]);

        // Booking flow is: Booking -> Payment -> Waiver (last).
        return redirect()
            ->route('user.bookings.review', $booking->id)
            ->with('success', 'Booking details saved. Choose a payment method to continue.');
    }

    /**
     * Show the customer's bookings list.
     *
     * GET /user/bookings
     */
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookings = Booking::where('user_id', session('user_id'))
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('visit_date')
            ->paginate(10)
            ->through(fn ($booking) => $this->formatBooking($booking));

        return view('user.bookings', compact('bookings'));
    }

    /**
     * Show a summary of a single booking before payment/confirmation.
     *
     * GET /user/bookings/{booking}/review
     */
    public function review(int $booking)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        return view('user.booking-review', ['booking' => $this->formatBooking($bookingModel)]);
    }
    /**
     * Confirm the chosen payment method for a booking (QR Ph or cash on-site).
     *
     * POST /user/bookings/{booking}/payment
     */
    public function confirmPayment(Request $request, int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:qrph,cash'],
            'receipt'        => ['required_if:payment_method,qrph', 'nullable', 'image', 'max:5120'],
        ]);

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        $updateData = [
            'payment_method' => $validated['payment_method'],
            'status' => $validated['payment_method'] === 'qrph'
                ? 'awaiting_verification'
                : 'pending_payment', // stays pending until paid on-site
        ];

        if ($validated['payment_method'] === 'qrph' && $request->hasFile('receipt')) {
            // Delete old receipt if re-uploading
            if ($bookingModel->receipt_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($bookingModel->receipt_path);
            }

            $updateData['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $bookingModel->update($updateData);

        $message = $validated['payment_method'] === 'qrph'
            ? 'Thanks! We are verifying your QR Ph payment. Please sign the waiver to finish your booking.'
            : 'Payment method noted — please settle at the counter upon arrival. Please sign the waiver to finish your booking.';

        // Waiver is the last step of the booking flow (Booking -> Payment -> Waiver).
        return redirect()
            ->route('user.waiver')
            ->with('success', $message);
    }


    /**
     * Cancel an existing booking.
     *
     * POST /user/bookings/{booking}/cancel
     */
    public function cancel(int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        $bookingModel->update(['status' => 'cancelled']);

        return redirect()
            ->route('user.bookings')
            ->with('success', 'Booking cancelled.');
    }

    /**
     * Show the date/time picker to reschedule an existing booking.
     *
     * GET /user/bookings/{booking}/reschedule
     */
    public function editReschedule(int $booking)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        $packageLabel = $this->packages[$bookingModel->service][$bookingModel->package]['name'] ?? $bookingModel->package;

        return view('user.reschedule', ['booking' => $bookingModel, 'packageLabel' => $packageLabel]);
    }

    /**
     * Save the new date/time chosen for an existing booking.
     *
     * POST /user/bookings/{booking}/reschedule
     */
    public function reschedule(Request $request, int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        $validated = $request->validate([
            'visit_date' => ['required', 'date'],
            'visit_time' => ['required', 'date_format:H:i'],
        ]);

        $bookingModel->update([
            'visit_date' => $validated['visit_date'],
            'visit_time' => $validated['visit_time'],
        ]);

        return redirect()
            ->route('user.bookings')
            ->with('success', 'Booking rescheduled.');
    }

    /**
     * Shape a Booking model into the display array expected by the booking
     * views: ['id','package','date','pax','status_label','status_class'].
     */
    private function formatBooking(Booking $booking): array
    {
        $meta = $this->statusMeta($booking->status);

        return [
            'id'           => $booking->id,
            'package'      => $this->packages[$booking->service][$booking->package]['name'] ?? $booking->package,
            'date'         => $booking->visit_date->format('M j, Y'),
            'pax'          => $booking->tier,
            'status_label' => $meta['label'],
            'status_class' => $meta['class'],
        ];
    }

    /**
     * Map a booking status to its display label + tag color class.
     */
    private function statusMeta(string $status): array
    {
        return match ($status) {
            'pending_payment'       => ['label' => 'Pending payment', 'class' => 'amber'],
            'awaiting_verification' => ['label' => 'Awaiting verification', 'class' => 'amber'],
            'confirmed'              => ['label' => 'Confirmed', 'class' => 'green'],
            'done'                   => ['label' => 'Done', 'class' => 'green'],
            'cancelled'              => ['label' => 'Cancelled', 'class' => 'rose'],
            default                  => ['label' => ucfirst(str_replace('_', ' ', $status)), 'class' => 'amber'],
        };
    }

    /**
     * Build the next 7 days for the date strip on the booking page.
     */
    protected function upcomingDates(): array
    {
        $dates = [];

        for ($i = 0; $i < 7; $i++) {
            $day = Carbon::today()->addDays($i);

            $dates[] = [
                'value' => $day->toDateString(),
                'day'   => $day->format('D'),
                'num'   => $day->format('j'),
            ];
        }

        return $dates;
    }
}