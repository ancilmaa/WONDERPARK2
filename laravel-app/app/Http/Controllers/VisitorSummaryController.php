<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VisitorSummaryController extends Controller
{
    // Added '0-12' so young children (e.g. 5-8 y/o at Dino Adventure) have a
    // bracket to fall into. Without this bracket they either get silently
    // dropped or lumped into 'Unknown'.
    protected array $ageBrackets = [
        '0-12'  => [0, 12],
        '13-17' => [13, 17],
        '18-24' => [18, 24],
        '25-34' => [25, 34],
        '35-44' => [35, 44],
        '45-54' => [45, 54],
        '55+'   => [55, 200],
    ];

    // Keyed by the actual raw value stored in bookings.service (confirmed via screenshot: snake_case slugs)
    protected array $attractionMeta = [
        'field_of_rides'  => ['name' => 'Field of Rides',  'icon' => 'fa-circle-half-stroke', 'color' => '#4C6FFF'],
        'dino_adventure'  => ['name' => 'Dino Adventure',  'icon' => 'fa-water',              'color' => '#1FA37A'],
        'rollerfever'     => ['name' => 'Roller Fever',    'icon' => 'fa-bolt',                'color' => '#FF7A45'],
    ];

    // Each age bracket maps to a LIST of promo variants so different
    // attractions sharing the same top bracket don't show identical promos.
    protected array $promoPlaybook = [
        '0-12' => [
            ['title' => 'Kiddie Explorer Bundle', 'desc' => 'Bundle in extra playground/slide access — most of this group are young children who love repeat play.'],
            ['title' => 'Add-On Slide Pass',      'desc' => 'Offer a discounted add-on for an extra slide or ride — this bracket skews young and benefits from more play variety.'],
        ],
        '13-17' => [
            ['title' => 'Student Squad Promo', 'desc' => 'Offer a discounted weekday rate for school groups of 10+ pax to fill mid-week slots with this bracket.'],
            ['title' => 'Field Trip Rate',      'desc' => 'Pitch a bulk-booking group rate to schools targeting this attraction for organized outings.'],
        ],
        '18-24' => [
            ['title' => 'Barkada Bundle',             'desc' => 'Push a 4-6 pax group package with a free photo pass — this age group books in groups and shares on social media.'],
            ['title' => 'Social Media Shoutout Deal', 'desc' => 'Offer a small discount for visitors who tag the attraction on social media before entry.'],
        ],
        '25-34' => [
            ['title' => 'Weekend Getaway Deal', 'desc' => 'Bundle tickets with a food voucher for young professionals looking for a weekend escape.'],
            ['title' => 'Couples Combo',        'desc' => 'Offer a 2-pax date package with a discount on a second attraction.'],
        ],
        '35-44' => [
            ['title' => 'Family Fun Package',   'desc' => 'Try a kids-go-free or 2-adults-2-kids bundle — this bracket is likely booking for their children.'],
            ['title' => 'Weekday Family Saver', 'desc' => 'Offer an off-peak weekday family bundle to spread out weekend crowding.'],
            ['title' => 'Group of 4+ Discount', 'desc' => 'Push a flat discount for families booking 4 or more tickets together.'],
        ],
        '45-54' => [
            ['title' => 'Weekday Leisure Rate',  'desc' => 'Offer off-peak weekday pricing — this group tends to prefer quieter, less crowded visits.'],
            ['title' => 'Relaxed Visit Package', 'desc' => 'Bundle a slower-paced attraction with a dining voucher for this bracket.'],
        ],
        '55+' => [
            ['title' => 'Senior Citizen Discount', 'desc' => 'Highlight your senior discount and lower-intensity attractions or packages for this bracket.'],
        ],
        'Unknown' => [
            ['title' => 'Encourage Profile Completion', 'desc' => 'Prompt these visitors to complete their age info at checkout so future promos can target them properly.'],
        ],
    ];

    public function index(Request $request)
    {
        $days = (int) $request->get('days', 30);
        $from = now()->subDays($days);

        $bookings = Booking::query()
            // A booking can come from either the customer flow (user_id) or
            // the admin/reservations flow (customer_id) — both point to
            // users.user_id, so match on whichever one is set.
            ->leftJoin('users', function ($join) {
                $join->on('users.user_id', '=', 'bookings.customer_id')
                     ->orOn('users.user_id', '=', 'bookings.user_id');
            })
            // Filter on the actual visit/reservation date, not created_at,
            // to match what the Reservations page shows for this range.
            ->whereRaw('COALESCE(bookings.reservation_date, bookings.visit_date) >= ?', [$from->toDateString()])
            ->select('bookings.*', 'users.age as user_age', 'users.email as user_email')
            ->addSelect(DB::raw('COALESCE(bookings.customer_name, users.fullname) as resolved_customer_name'))
            ->addSelect(DB::raw('COALESCE(bookings.customer_id, bookings.user_id) as resolved_user_id'))
            ->orderByDesc('bookings.created_at')
            ->get();

        $totalOnlineBookings = $bookings->count();
        $totalVisitors = SiteVisit::where('last_seen_at', '>=', $from)->count();

        // --- Age distribution (now counts PEOPLE, not bookings) ---
        $overallAgeCounts = $this->ageCountsForBookings($bookings);
        $totalPeopleCounted = array_sum($overallAgeCounts);

        $ageDistribution = collect($overallAgeCounts)
            ->filter(fn ($count, $label) => $label !== 'Unknown' || $count > 0)
            ->map(function ($count, $label) use ($totalPeopleCounted) {
                return [
                    'label'      => $label,
                    'count'      => $count,
                    'percentage' => $totalPeopleCounted > 0 ? round(($count / $totalPeopleCounted) * 100, 1) : 0,
                ];
            })
            ->values();

        $topOverallAgeGroup = $ageDistribution->sortByDesc('count')->first()['label'] ?? '—';

        $attractionRanking = $bookings
            ->groupBy('service')
            ->map(function ($group, $serviceCode) {
                $meta = $this->attractionMeta[$serviceCode] ?? [
                    'name'  => $serviceCode ? $this->prettifyPackageCode($serviceCode) : 'Other',
                    'icon'  => 'fa-ticket',
                    'color' => '#6B7280',
                ];

                $groupAgeCounts = $this->ageCountsForBookings($group);

                $ageBreakdown = collect($groupAgeCounts)
                    ->filter(fn ($count, $label) => $label !== 'Unknown' || $count > 0)
                    ->map(fn ($count, $label) => ['label' => $label, 'count' => $count])
                    ->values();

                $topAge = $ageBreakdown->sortByDesc('count')->first()['label'] ?? '—';

                $packageBreakdown = $group
                    ->groupBy('package')
                    ->map(function ($pkgGroup, $packageCode) {
                        return [
                            'label' => $this->prettifyPackageCode($packageCode ?: 'Unspecified'),
                            'count' => $pkgGroup->count(),
                        ];
                    })
                    ->sortByDesc('count')
                    ->values();

                $topPackage = $packageBreakdown->first()['label'] ?? '—';
                $maxPackageCount = $packageBreakdown->first()['count'] ?? 0;
                $totalBookings = $group->count();

                return [
                    'name'              => $meta['name'],
                    'icon'              => $meta['icon'],
                    'color'             => $meta['color'],
                    // 'bookings' is what the view reads; 'total_bookings' kept
                    // for internal use further down (sorting, service pie, promos).
                    'bookings'          => $totalBookings,
                    'total_bookings'    => $totalBookings,
                    'top_age_group'     => $topAge,
                    'top_package'       => $topPackage,
                    'package_breakdown' => $packageBreakdown->take(3)->values(),
                    'max_package_count' => $maxPackageCount ?: 1,
                ];
            })
            ->sortByDesc('total_bookings')
            ->values();

        $topAttraction = $attractionRanking->first()['name'] ?? '—';

        // Service distribution for the pie chart — percentage + raw count per service.
        $serviceDistribution = $attractionRanking
            ->map(function ($a) use ($totalOnlineBookings) {
                return [
                    'name'       => $a['name'],
                    'count'      => $a['total_bookings'],
                    'percentage' => $totalOnlineBookings > 0
                        ? round(($a['total_bookings'] / $totalOnlineBookings) * 100, 1)
                        : 0,
                    'color'      => $a['color'],
                ];
            })
            ->values();

        // Most-availed packages overall (across all services), ranked 1-5.
        $packageRanking = $bookings
            ->groupBy('package')
            ->map(function ($group, $packageCode) {
                $dominantService = $group->pluck('service')->countBy()->sortDesc()->keys()->first();
                $meta = $this->attractionMeta[$dominantService] ?? [
                    'name'  => $dominantService ? $this->prettifyPackageCode($dominantService) : 'Other',
                    'icon'  => 'fa-ticket',
                    'color' => '#6B7280',
                ];

                return [
                    'package' => $this->prettifyPackageCode($packageCode ?: 'Unspecified'),
                    'service' => $meta['name'],
                    'color'   => $meta['color'],
                    'icon'    => $meta['icon'],
                    'count'   => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Pick a promo variant per attraction based on its top age bracket
        // (which now reflects actual attendees when age_breakdown is filled
        // in, not just the booker's account age). Uses a stable hash of the
        // attraction name so the SAME attraction always shows the SAME promo
        // across refreshes.
        $recommendations = $attractionRanking
            ->filter(fn ($a) => $a['total_bookings'] > 0 && isset($this->promoPlaybook[$a['top_age_group']]))
            ->map(function ($a) {
                $variants = $this->promoPlaybook[$a['top_age_group']];
                $index = crc32($a['name']) % count($variants);
                $promo = $variants[$index];

                return [
                    'attraction' => $a['name'],
                    'icon'       => $a['icon'],
                    'color'      => $a['color'],
                    'age_group'  => $a['top_age_group'],
                    'title'      => $promo['title'],
                    'desc'       => $promo['desc'],
                ];
            })
            ->values();

        // Visitors currently considered "online" — anyone with a site_visits
        // heartbeat in the last 5 minutes, matched by user id.
        $onlineUserIds = SiteVisit::where('last_seen_at', '>=', now()->subMinutes(5))
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Only reservations that haven't happened yet (today or a future
        // date) belong in the "Recent Visitor Logins" panel — a booking
        // dated before today is already done, so exclude it here.
        $today = now()->startOfDay();
        $upcomingBookings = $bookings->filter(function ($b) use ($today) {
            $date = $b->reservation_date ?? $b->visit_date;
            if (!$date) {
                return false;
            }

            return \Carbon\Carbon::parse($date)->startOfDay()->gte($today);
        });

        // One row per visitor (not per booking). Bookings are already ordered
        // newest-first, so grouping by identity and taking the first item in
        // each group keeps that visitor's most recent upcoming login/booking only.
        $visitorLogins = $upcomingBookings
            ->groupBy(function ($b) {
                // Prefer a stable identity: user id, then email, then name.
                return $b->resolved_user_id ?: ($b->user_email ?: $b->resolved_customer_name);
            })
            ->map(function ($group) use ($onlineUserIds) {
                $b = $group->first();
                $name = $b->resolved_customer_name ?: ('Visitor #' . $b->resolved_user_id);

                return [
                    'id'             => $b->id,
                    'name'           => $name,
                    'initials'       => $this->initialsFromName($name),
                    'email'          => $b->user_email,
                    'age_group'      => $this->resolveAgeGroup($b->user_age) ?? 'Unknown',
                    'last_login'     => optional($b->created_at)->format('M d, Y h:i A'),
                    'online'         => $b->resolved_user_id ? in_array($b->resolved_user_id, $onlineUserIds) : false,
                    'reservation_id' => $b->id,
                    '_sort'          => $b->created_at,
                ];
            })
            ->sortByDesc('_sort')
            ->take(50)
            ->map(fn ($v) => collect($v)->except('_sort')->all())
            ->values();

        $newAccounts = User::where('created_at', '>=', $from)->get();
        $totalNewAccounts = $newAccounts->count();

        $accountAgeDistribution = collect($this->ageBrackets)
            ->map(function ($range, $label) use ($newAccounts) {
                [$min, $max] = $range;
                return [
                    'label' => $label,
                    'count' => $newAccounts->filter(fn ($u) => $this->ageInRange($u->age, $min, $max))->count(),
                ];
            })
            ->values();

        $unknownNewAccountAge = $newAccounts->filter(fn ($u) => $this->resolveAgeGroup($u->age) === null)->count();
        if ($unknownNewAccountAge > 0) {
            $accountAgeDistribution->push(['label' => 'Unknown', 'count' => $unknownNewAccountAge]);
        }

        return view('customer.visitor-summary', compact(
            'days',
            'totalOnlineBookings',
            'totalVisitors',
            'topOverallAgeGroup',
            'topAttraction',
            'attractionRanking',
            'serviceDistribution',
            'packageRanking',
            'recommendations',
            'visitorLogins',
            'totalNewAccounts',
            'accountAgeDistribution',
            'ageDistribution'
        ));
    }

    public function liveCount()
    {
        $onlineSince = now()->subMinutes(5);

        return response()->json([
            'online_now' => SiteVisit::where('last_seen_at', '>=', $onlineSince)->count(),
        ]);
    }

    /**
     * Sums per-bracket attendee counts across a collection of bookings.
     * Each booking contributes via bookingAgeCounts() — either its real
     * age_breakdown (preferred) or a 1-person fallback based on the
     * booker's account age.
     */
    protected function ageCountsForBookings($bookings): array
    {
        $labels = array_merge(array_keys($this->ageBrackets), ['Unknown']);
        $totals = array_fill_keys($labels, 0);

        foreach ($bookings as $booking) {
            foreach ($this->bookingAgeCounts($booking) as $label => $count) {
                $totals[$label] += $count;
            }
        }

        return $totals;
    }

    /**
     * Returns [bracket_label => count] for ONE booking.
     *
     * If the booking has a filled-in age_breakdown (new bookings, once the
     * form asks for it), that's used directly — it can represent multiple
     * attendees across several brackets (e.g. 2 kids + 1 parent).
     *
     * Otherwise, falls back to the old behavior: count the booking as ONE
     * person, bracketed by the account holder's age (users.age). This keeps
     * every booking made before this feature existed working exactly as
     * it did before.
     */
    protected function bookingAgeCounts($booking): array
    {
        $labels = array_merge(array_keys($this->ageBrackets), ['Unknown']);
        $counts = array_fill_keys($labels, 0);

        $raw = $booking->age_breakdown ?? null;
        $decoded = is_array($raw) ? $raw : (is_string($raw) ? json_decode($raw, true) : null);

        if (is_array($decoded) && array_sum(array_map('intval', $decoded)) > 0) {
            foreach ($this->ageBrackets as $label => $range) {
                $counts[$label] = (int) ($decoded[$label] ?? 0);
            }
            $counts['Unknown'] = (int) ($decoded['Unknown'] ?? 0);

            return $counts;
        }

        // Fallback: single person, bracketed by the account holder's age.
        $label = $this->resolveAgeGroup($booking->user_age);
        if ($label !== null) {
            $counts[$label] = 1;
        } else {
            $counts['Unknown'] = 1;
        }

        return $counts;
    }

    protected function ageInRange(mixed $age, int $min, int $max): bool
    {
        if ($age === null || $age === '' || !is_numeric($age)) {
            return false;
        }

        $age = (int) $age;

        return $age >= $min && $age <= $max;
    }

    protected function resolveAgeGroup(mixed $age): ?string
    {
        foreach ($this->ageBrackets as $label => [$min, $max]) {
            if ($this->ageInRange($age, $min, $max)) {
                return $label;
            }
        }

        return null;
    }

    protected function prettifyPackageCode(string $code): string
    {
        $label = str_replace('_', ' ', $code);
        $label = preg_replace('/(\d+)([a-zA-Z])/', '$1 $2', $label);

        return ucwords(trim($label));
    }

    protected function initialsFromName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $parts = array_filter($parts, fn ($p) => $p !== '');

        if (empty($parts)) {
            return '?';
        }

        if (count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 2));
        }

        $first = strtoupper(substr(reset($parts), 0, 1));
        $last  = strtoupper(substr(end($parts), 0, 1));

        return $first . $last;
    }
}