<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VisitorSummaryController extends Controller
{
    protected array $ageBrackets = [
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

    protected array $promoPlaybook = [
        '13-17'   => ['title' => 'Student Squad Promo',     'desc' => 'Offer a discounted weekday rate for school groups of 10+ pax to fill mid-week slots with this bracket.'],
        '18-24'   => ['title' => 'Barkada Bundle',           'desc' => 'Push a 4-6 pax group package with a free photo pass — this age group books in groups and shares on social media.'],
        '25-34'   => ['title' => 'Weekend Getaway Deal',     'desc' => 'Bundle tickets with a food voucher for young professionals looking for a weekend escape.'],
        '35-44'   => ['title' => 'Family Fun Package',       'desc' => 'Try a kids-go-free or 2-adults-2-kids bundle — this bracket is likely booking for their children.'],
        '45-54'   => ['title' => 'Weekday Leisure Rate',     'desc' => 'Offer off-peak weekday pricing — this group tends to prefer quieter, less crowded visits.'],
        '55+'     => ['title' => 'Senior Citizen Discount',  'desc' => 'Highlight your senior discount and lower-intensity attractions or packages for this bracket.'],
        'Unknown' => ['title' => 'Encourage Profile Completion', 'desc' => 'Prompt these visitors to complete their age info at checkout so future promos can target them properly.'],
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
            ->select('bookings.*', 'users.age as user_age')
            ->addSelect(DB::raw('COALESCE(bookings.customer_name, users.fullname) as resolved_customer_name'))
            ->orderByDesc('bookings.created_at')
            ->get();

        $totalOnlineBookings = $bookings->count();
        $totalVisitors = SiteVisit::where('last_seen_at', '>=', $from)->count();

        $ageDistribution = collect($this->ageBrackets)
            ->map(function ($range, $label) use ($bookings) {
                [$min, $max] = $range;
                $count = $bookings->filter(fn ($b) => $this->ageInRange($b->user_age, $min, $max))->count();
                return ['label' => $label, 'count' => $count];
            })
            ->values();

        $unknownAgeCount = $bookings->filter(fn ($b) => $this->resolveAgeGroup($b->user_age) === null)->count();
        if ($unknownAgeCount > 0) {
            $ageDistribution->push(['label' => 'Unknown', 'count' => $unknownAgeCount]);
        }

        $topOverallAgeGroup = $ageDistribution->sortByDesc('count')->first()['label'] ?? '—';

        $attractions = $bookings
            ->groupBy('service')
            ->map(function ($group, $serviceCode) {
                $meta = $this->attractionMeta[$serviceCode] ?? [
                    'name'  => $serviceCode ? $this->prettifyPackageCode($serviceCode) : 'Other',
                    'icon'  => 'fa-ticket',
                    'color' => '#6B7280',
                ];

                $ageBreakdown = collect($this->ageBrackets)
                    ->map(function ($range, $label) use ($group) {
                        [$min, $max] = $range;
                        return [
                            'label' => $label,
                            'count' => $group->filter(fn ($b) => $this->ageInRange($b->user_age, $min, $max))->count(),
                        ];
                    })
                    ->values();

                $unknownCount = $group->filter(fn ($b) => $this->resolveAgeGroup($b->user_age) === null)->count();
                if ($unknownCount > 0) {
                    $ageBreakdown->push(['label' => 'Unknown', 'count' => $unknownCount]);
                }

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

                return [
                    'name'              => $meta['name'],
                    'icon'              => $meta['icon'],
                    'color'             => $meta['color'],
                    'total_bookings'    => $group->count(),
                    'top_age_group'     => $topAge,
                    'top_package'       => $topPackage,
                    'package_breakdown' => $packageBreakdown->take(3)->values(),
                    'max_package_count' => $maxPackageCount ?: 1,
                ];
            })
            ->sortByDesc('total_bookings')
            ->values();

        $topAttraction = $attractions->first()['name'] ?? '—';

        // Service distribution for the pie chart — percentage + raw count per service.
        $serviceDistribution = $attractions
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

        $recommendations = $attractions
            ->filter(fn ($a) => $a['total_bookings'] > 0 && isset($this->promoPlaybook[$a['top_age_group']]))
            ->map(function ($a) {
                $promo = $this->promoPlaybook[$a['top_age_group']];
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

        $visitors = $bookings->take(50)->map(function ($b) {
            return [
                'id'             => $b->id,
                'name'           => $b->resolved_customer_name ?: ('Visitor #' . $b->user_id),
                'age_group'      => $this->resolveAgeGroup($b->user_age) ?? 'Unknown',
                'login_at'       => optional($b->created_at)->format('M d, Y h:i A'),
                'reservation_id' => $b->id,
            ];
        })->values();

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
            'attractions',
            'serviceDistribution',
            'packageRanking',
            'recommendations',
            'visitors',
            'totalNewAccounts',
            'accountAgeDistribution'
        ));
    }

    public function liveCount()
    {
        $onlineSince = now()->subMinutes(5);

        return response()->json([
            'online_now' => SiteVisit::where('last_seen_at', '>=', $onlineSince)->count(),
        ]);
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
}