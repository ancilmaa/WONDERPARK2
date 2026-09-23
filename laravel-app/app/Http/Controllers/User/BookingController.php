<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\BookingCatalog;
use App\Support\BookingPricingOverrides;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Services, packages, add-ons and inclusions — pulled from
     * App\Support\BookingCatalog with any admin-saved price overrides
     * (App\Support\BookingPricingOverrides) layered on top, so this
     * customer-facing controller and BookingCmsController always show
     * the exact same prices. Previously these were hardcoded on this
     * class directly, which is why CMS edits never showed up here and
     * "reset to default" had nothing to reset on this side either.
     */
    protected array $services;
    protected array $packages;
    protected array $addons;
    protected array $inclusions;

    public function __construct()
    {
        $this->services   = BookingCatalog::services();
        $this->packages   = BookingPricingOverrides::applyToPackages(BookingCatalog::basePackages());
        $this->addons     = BookingPricingOverrides::applyToAddons(BookingCatalog::baseAddons());
        $this->inclusions = BookingCatalog::inclusions();
    }

    /**
     * Show the booking form (date + package selection).
     *
     * GET /user/booking
     */
    public function create()
    {
        $dates = $this->upcomingDates();
        $services = $this->services;
        $packages = $this->packages;
        $inclusions = $this->inclusions;
        $addons = $this->addons;

        return view('user.booking', compact('dates', 'services', 'packages', 'inclusions', 'addons'));
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
        // the correct waiver text. Also remember the booking id so the
        // receipt shown after the waiver is signed knows which booking to
        // display.
        session([
            'booking_service' => $validated['service'],
            'booking_id'       => $booking->id,
        ]);

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
            ->paginate(5)
            ->through(fn ($booking) => $this->formatBooking($booking));

        $stats = $this->bookingStats(session('user_id'));

        return view('user.bookings', compact('bookings', 'stats'));
    }

    /**
     * Personal booking stats for the "My Bookings" page — the customer's
     * own activity, not overall business performance: total visits,
     * upcoming vs completed reservations, total spend, most-booked
     * package, and a booking-count trend. "Visits" here means
     * 'confirmed' + 'done' bookings — pending/awaiting-verification ones
     * aren't a real visit yet, and cancelled ones are excluded.
     */
    private function bookingStats(int $userId): array
    {
        $upcoming = Booking::where('user_id', $userId)->where('status', 'confirmed')->get();
        $completed = Booking::where('user_id', $userId)->where('status', 'done')->get();
        $counted = $upcoming->concat($completed);

        $favoritePackageKey = $counted
            ->countBy(fn ($booking) => $booking->service . '|' . $booking->package)
            ->sortDesc()
            ->keys()
            ->first();

        $favoritePackageName = null;
        $favoritePackageCount = 0;

        if ($favoritePackageKey) {
            [$favService, $favPackage] = explode('|', $favoritePackageKey, 2);
            $favoritePackageName = $this->packages[$favService][$favPackage]['name'] ?? $favPackage;
            $favoritePackageCount = $counted->filter(
                fn ($booking) => $booking->service === $favService && $booking->package === $favPackage
            )->count();
        }

        $bookingsByMonth = $counted->groupBy(fn ($booking) => $booking->visit_date->format('Y-m'))
            ->map(fn ($group) => $group->count());

        $monthLabels = [];
        $monthValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthLabels[] = $month->format('M');
            $monthValues[] = (int) ($bookingsByMonth[$month->format('Y-m')] ?? 0);
        }

        // Category breakdown (Dino Adventure / RollerFever / Field of
        // Rides) — a proportion-of-whole question, so this feeds a donut
        // chart rather than the bar chart above (which is a trend over
        // time and reads better as bars).
        $bookingsByCategory = $counted->countBy(fn ($booking) => $booking->service);

        $categoryLabels = [];
        $categoryValues = [];

        foreach ($bookingsByCategory as $serviceCode => $count) {
            $categoryLabels[] = $this->services[$serviceCode]['name'] ?? ucfirst(str_replace('_', ' ', $serviceCode));
            $categoryValues[] = $count;
        }

        return [
            'total_visits'            => $counted->count(),
            'upcoming_reservations'   => $upcoming->count(),
            'completed_reservations'  => $completed->count(),
            'total_spending'          => $counted->sum('price'),
            'favorite_package'        => $favoritePackageName,
            'favorite_package_count'  => $favoritePackageCount,
            'history_labels'          => $monthLabels,
            'history_values'          => $monthValues,
            'category_labels'         => $categoryLabels,
            'category_values'         => $categoryValues,
        ];
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

    public function receipt(int $booking)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        $service = $this->services[$bookingModel->service] ?? null;
        $package = $this->packages[$bookingModel->service][$bookingModel->package] ?? null;

        return view('user.booking-receipt', [
            'booking' => [
                'id'             => $bookingModel->id,
                'service_name'   => $service['name'] ?? ucfirst(str_replace('_', ' ', $bookingModel->service)),
                'package_name'   => $package['name'] ?? $bookingModel->package,
                'date'           => $bookingModel->visit_date->format('M j, Y'),
                'time'           => $bookingModel->visit_time,
                'pax'            => $bookingModel->tier,
                'price'          => '₱' . number_format((float) $bookingModel->price, 2),
                'payment_method' => strtoupper($bookingModel->payment_method ?? ''),
                'voucher_code'   => $bookingModel->voucher_code,
            ],
        ]);
    }

    /**
     * Confirm the chosen payment method for a booking (QR Ph, GCash, or
     * Maya — bookings require online payment upfront to guarantee the
     * slot). Payment confirms the booking immediately — no admin/staff
     * verification step. Booking flow: Booking -> Payment (confirmed
     * right away) -> Waiver -> Receipt with voucher code.
     *
     * POST /user/bookings/{booking}/payment
     */
    public function confirmPayment(Request $request, int $booking): RedirectResponse
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:qrph,gcash,maya'],
            'receipt'        => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'receipt.required' => 'Please upload a screenshot or receipt of your payment first.',
            'receipt.mimes'    => 'Proof of payment must be an image (JPG/PNG/WEBP) or PDF.',
            'receipt.max'      => 'Proof of payment must be 5MB or smaller.',
        ]);

        $bookingModel = Booking::where('user_id', session('user_id'))->find($booking);

        abort_if(!$bookingModel, 404);

        // Only pending bookings can have payment submitted against them —
        // stops someone from re-submitting proof against an already
        // confirmed/cancelled/done booking.
        if (!in_array($bookingModel->status, ['pending_payment', 'awaiting_verification'])) {
            return redirect()
                ->route('user.bookings')
                ->with('error', 'This booking is no longer awaiting payment.');
        }

        // QR Ph payment confirms the booking right away — no more
        // "awaiting_verification" hold. The uploaded screenshot is still
        // kept on file for the cashier/admin's records, it just doesn't
        // block confirmation anymore.
        $path = $request->file('receipt')->store('payment-proofs', 'public');

        $bookingModel->update([
            'payment_method'       => $validated['payment_method'],
            'receipt_path'         => $path,
            'payment_proof_path'   => $path,
            'payment_submitted_at' => now(),
            'status'               => 'confirmed',
        ]);

        // Waiver is still the last step of the booking flow (Booking ->
        // Payment -> Waiver); payment is already confirmed at this point.
        return redirect()
            ->route('user.waiver')
            ->with('success', 'Payment confirmed! Please sign the waiver to finish your booking.');
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

        if (in_array($bookingModel->status, ['confirmed', 'done'])) {
            return redirect()
                ->route('user.bookings')
                ->with('error', 'Confirmed bookings can no longer be cancelled. Please reschedule instead.');
        }

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
     * views: ['id','category','package','date','pax','status_label','status_class'].
     */
    private function formatBooking(Booking $booking): array
    {
        $meta = $this->statusMeta($booking->status);

        return [
            'id'           => $booking->id,
            'category'     => $this->services[$booking->service]['name'] ?? ucfirst(str_replace('_', ' ', $booking->service)),
            'package'      => $this->packages[$booking->service][$booking->package]['name'] ?? $booking->package,
            'date'         => $booking->visit_date->format('M j, Y'),
            'time'         => $booking->visit_time,
            'pax'          => $booking->tier,
            'price'        => '₱' . number_format((float) $booking->price, 2),
            'status'       => $booking->status,
            'status_label' => $meta['label'],
            'status_class' => $meta['class'],
            'voucher_code' => $booking->voucher_code,
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

    public function packageInfo(string $service, string $package): array
    {
        return [
            'service_name' => $this->services[$service]['name'] ?? ucfirst(str_replace('_', ' ', $service)),
            'package_name' => $this->packages[$service][$package]['name'] ?? $package,
        ];
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