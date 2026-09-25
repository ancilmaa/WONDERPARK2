@extends('layouts.sidebar')

@section('title', 'Booking & Pricing')

@section('styles')
<style>
    /* ---------- local tokens ---------- */
    :root {
        --surface-2: #FBF9FC;
        --ring: 0 0 0 4px rgba(255, 92, 133, .14);
        --ease: cubic-bezier(.2, .7, .3, 1);
    }

    /* ---------- crumb ---------- */
    .cms-crumb {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: .74rem;
        color: var(--muted);
        margin-bottom: 8px;
    }
    .cms-crumb a { color: var(--muted); text-decoration: none; transition: color .15s; }
    .cms-crumb a:hover { color: var(--pink-dark); }
    .cms-crumb i { font-size: .5rem; color: var(--line-strong); }
    .cms-crumb .is-current { color: var(--ink-soft); font-weight: 600; }

    /* ---------- toolbar ---------- */
    .cms-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        background: linear-gradient(180deg, #FFFFFF 0%, #FFFCFD 100%);
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 14px 20px;
        position: sticky;
        top: 8px;
        z-index: 20;
        box-shadow: 0 1px 2px rgba(20, 16, 28, .04), 0 14px 32px -12px rgba(20, 16, 28, .10);
        backdrop-filter: saturate(1.4) blur(8px);
    }
    .cms-toolbar-main { min-width: 220px; }
    .cms-toolbar h1 {
        font-size: 1.18rem;
        font-weight: 800;
        color: var(--ink);
        margin: 0 0 2px;
        letter-spacing: -.02em;
        background: linear-gradient(90deg, var(--ink) 0%, var(--pink-deep) 140%);
        -webkit-background-clip: text;
        background-clip: text;
    }
    .cms-toolbar p { font-size: .76rem; color: var(--muted); margin: 0; max-width: 46ch; line-height: 1.4; }

    .cms-toolbar-stats {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .cms-stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .71rem;
        font-weight: 700;
        color: var(--ink-soft);
        background: var(--surface-2);
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: 6px 13px 6px 11px;
        white-space: nowrap;
        transition: transform .15s var(--ease), box-shadow .15s;
    }
    .cms-stat-chip:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(20,16,28,.08); }
    .cms-stat-chip i { font-size: .66rem; color: var(--muted); }
    .cms-stat-chip.is-highlight {
        background: linear-gradient(135deg, var(--pink-pale) 0%, var(--pink-light) 100%);
        border-color: rgba(255, 92, 133, .25);
        color: var(--pink-deep);
    }
    .cms-stat-chip.is-highlight i { color: var(--pink); }
    .cms-stat-chip strong { color: var(--ink); font-weight: 800; }
    .cms-stat-chip.is-highlight strong { color: var(--pink-deep); }

    .cms-toolbar-actions { display: flex; align-items: center; gap: 10px; }

    .cms-dirty-flag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .7rem;
        font-weight: 700;
        color: var(--pink-dark);
        opacity: 0;
        transform: translateY(2px);
        transition: opacity .18s var(--ease), transform .18s var(--ease);
        pointer-events: none;
        white-space: nowrap;
    }
    .cms-dirty-flag.is-visible { opacity: 1; transform: translateY(0); }
    .cms-dirty-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--pink);
        box-shadow: 0 0 0 3px rgba(255, 92, 133, .18);
        animation: cms-pulse 1.6s ease-in-out infinite;
    }
    @keyframes cms-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

    .cms-btn-ghost, .cms-btn-solid {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-family: inherit;
        font-size: .81rem;
        font-weight: 700;
        padding: 10px 18px;
        border-radius: 11px;
        cursor: pointer;
        white-space: nowrap;
        border: none;
        transition: transform .12s var(--ease), box-shadow .15s, background .15s, opacity .15s;
    }
    .cms-btn-ghost { background: var(--card); color: var(--ink-soft); border: 1.5px solid var(--line-strong); }
    .cms-btn-ghost:hover { border-color: var(--pink); color: var(--pink-dark); }
    .cms-btn-solid {
        background: linear-gradient(135deg, var(--pink) 0%, var(--pink-dark) 100%);
        color: #fff;
        box-shadow: 0 10px 22px -6px rgba(214, 62, 99, .55);
    }
    .cms-btn-solid:hover { transform: translateY(-1px); box-shadow: 0 14px 26px -6px rgba(214, 62, 99, .62); }
    .cms-btn-solid:active { transform: translateY(0); }
    .cms-btn-solid:disabled { opacity: .55; cursor: default; transform: none; box-shadow: none; }
    .cms-btn-solid .fa-spinner { animation: cms-spin .7s linear infinite; }
    @keyframes cms-spin { to { transform: rotate(360deg); } }

    .cms-alert {
        padding: 12px 16px;
        border-radius: 12px;
        font-size: .83rem;
        font-weight: 600;
        margin-top: 12px;
        border: 1px solid transparent;
    }
    .cms-alert--success { background: var(--green-light); color: var(--green); border-color: rgba(31, 174, 158, .18); }
    .cms-alert--error { background: var(--pink-light); color: var(--pink-deep); border-color: rgba(214, 62, 99, .18); }

    /* ---------- service tabs ---------- */
    .booking-service-tabs {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
        margin-top: 14px;
        padding: 6px;
        background: var(--surface-2);
        border: 1px solid var(--line);
        border-radius: 16px;
    }
    .booking-service-tab {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        border-radius: 12px;
        border: none;
        background: transparent;
        color: var(--ink-soft);
        font-size: .81rem;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        flex: 1;
        min-width: 180px;
        justify-content: flex-start;
        transition: background .18s var(--ease), color .18s, box-shadow .18s;
    }
    .booking-service-tab:hover:not(.is-active) { background: rgba(255, 255, 255, .7); }
    .booking-service-tab .svc-tab-icon {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        background: var(--pink-pale);
        color: var(--pink-deep);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .82rem;
        flex-shrink: 0;
        transition: background .18s, color .18s, transform .18s var(--ease);
    }
    .booking-service-tab .svc-tab-text { display: flex; flex-direction: column; align-items: flex-start; gap: 1px; min-width: 0; }
    .booking-service-tab .svc-tab-text small { font-weight: 600; opacity: .65; font-size: .67rem; }
    .booking-service-tab .svc-tab-badge {
        margin-left: auto;
        font-size: .6rem;
        font-weight: 800;
        background: var(--pink-light);
        color: var(--pink-deep);
        border-radius: 999px;
        padding: 3px 9px;
        flex-shrink: 0;
    }
    .booking-service-tab.is-active {
        background: var(--card);
        color: var(--ink);
        box-shadow: 0 1px 2px rgba(20,16,28,.04), 0 10px 22px -8px rgba(20,16,28,.18);
    }
    .booking-service-tab.is-active .svc-tab-icon {
        background: linear-gradient(135deg, var(--pink) 0%, var(--pink-dark) 100%);
        color: #fff;
        transform: scale(1.04);
    }
    .booking-service-tab.is-active .svc-tab-badge { background: var(--pink); color: #fff; }

    .booking-service-panel[hidden] { display: none; }

    /* ---------- layout: packages (main) + add-ons (aside) ---------- */
    .booking-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        gap: 14px;
        align-items: start;
        margin-top: 14px;
    }
    .booking-aside {
        display: flex;
        flex-direction: column;
        gap: 14px;
        position: sticky;
        top: 88px;
    }

    @media (max-width: 1024px) {
        .booking-layout { grid-template-columns: 1fr; }
        .booking-aside { position: static; }
    }

    /* ---------- panel ---------- */
    .cms-panel {
        background: var(--card);
        border-radius: 16px;
        border: 1px solid var(--line);
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(20, 16, 28, .03);
    }
    .cms-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px 16px;
        border-bottom: 1px solid var(--line);
        background: linear-gradient(180deg, #FCFAFD 0%, #FFFFFF 100%);
    }
    .cms-panel-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .71rem;
        font-weight: 800;
        letter-spacing: .06em;
        color: var(--ink);
        text-transform: uppercase;
    }
    .cms-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--pink); flex-shrink: 0;
        box-shadow: 0 0 0 4px rgba(255, 92, 133, .14);
    }
    .cms-panel-head-right { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .cms-mini-chip {
        font-size: .65rem;
        font-weight: 700;
        color: var(--ink-soft);
        background: var(--surface-2);
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: 3px 9px;
        white-space: nowrap;
    }
    .cms-mini-chip.is-flag { color: var(--pink-deep); background: var(--pink-pale); border-color: rgba(255,92,133,.22); }
    .cms-panel-hint { font-size: .71rem; color: var(--muted); }
    .cms-panel-body { padding: 14px 16px; }

    /* ---------- package group ---------- */
    .booking-category-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .63rem;
        font-weight: 800;
        letter-spacing: .07em;
        text-transform: uppercase;
        color: var(--ink-soft);
        margin: 16px 0 8px;
    }
    .booking-category-label:first-child { margin-top: 0; }
    .booking-category-label::before {
        content: '';
        width: 14px;
        height: 3px;
        border-radius: 3px;
        background: linear-gradient(90deg, var(--pink) 0%, var(--pink-light) 100%);
        flex-shrink: 0;
    }
    .booking-category-label .booking-category-count {
        margin-left: auto;
        font-weight: 700;
        color: var(--muted);
        letter-spacing: 0;
        text-transform: none;
        font-size: .67rem;
        background: var(--surface-2, var(--bg));
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: 2px 9px;
    }

    .booking-package-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }

    .booking-package-card {
        position: relative;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 12px 13px 12px 15px;
        background: linear-gradient(180deg, var(--surface-2, var(--bg)) 0%, var(--bg) 100%);
        display: flex;
        flex-direction: column;
        transition: box-shadow .18s var(--ease), border-color .18s, transform .18s var(--ease);
    }
    .booking-package-card::before {
        content: '';
        position: absolute;
        left: 0; top: 10px; bottom: 10px;
        width: 3px;
        border-radius: 3px;
        background: var(--line-strong);
    }
    .booking-package-card[data-category="solo"]::before { background: linear-gradient(180deg, var(--pink), var(--pink-dark)); }
    .booking-package-card[data-category="bundle"]::before { background: linear-gradient(180deg, var(--amber), #C97A1D); }
    .booking-package-card[data-category="packages"]::before { background: linear-gradient(180deg, var(--green), #16897B); }
    .booking-package-card:hover {
        border-color: var(--line-strong);
        box-shadow: 0 1px 2px rgba(20,16,28,.04), 0 14px 26px -14px rgba(20,16,28,.28);
        transform: translateY(-1px);
    }

    .booking-package-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }
    .booking-package-head h3 {
        font-size: .85rem;
        font-weight: 700;
        color: var(--ink);
        margin: 0 0 2px;
        line-height: 1.3;
        letter-spacing: -.01em;
    }
    .booking-package-head p {
        font-size: .69rem;
        color: var(--muted);
        margin: 0;
        line-height: 1.35;
    }

    .booking-package-hint {
        font-size: .64rem;
        font-weight: 700;
        color: var(--pink-dark);
        background: var(--pink-pale);
        border: 1px solid rgba(255, 92, 133, .18);
        border-radius: 7px;
        padding: 3px 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 8px;
        width: fit-content;
    }

    .booking-reset-btn {
        font-family: inherit;
        font-size: .67rem;
        font-weight: 700;
        color: var(--pink-deep);
        background: var(--pink-light);
        border: none;
        border-radius: 8px;
        padding: 5px 10px;
        cursor: pointer;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        position: relative;
        transition: background .15s, color .15s, transform .12s var(--ease);
    }
    .booking-reset-btn:hover { background: var(--pink); color: #fff; transform: translateY(-1px); }
    .booking-reset-btn:disabled { opacity: .6; cursor: default; transform: none; }

    /* non-blocking confirm popover (replaces window.confirm, which can be
       silently suppressed by the browser and makes the button look dead) */
    .cms-confirm-pop {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        z-index: 30;
        background: var(--card, #fff);
        border: 1px solid var(--line-strong);
        border-radius: 12px;
        box-shadow: 0 10px 28px -8px rgba(20,16,28,.35);
        padding: 10px;
        width: 180px;
        font-size: .7rem;
        color: var(--ink-soft);
        text-align: left;
    }
    .cms-confirm-pop p { margin: 0 0 8px; font-weight: 600; line-height: 1.35; }
    .cms-confirm-pop-actions { display: flex; gap: 6px; }
    .cms-confirm-pop-actions button {
        flex: 1;
        font-family: inherit;
        font-size: .68rem;
        font-weight: 700;
        border: none;
        border-radius: 7px;
        padding: 6px 0;
        cursor: pointer;
    }
    .cms-confirm-yes { background: var(--pink); color: #fff; }
    .cms-confirm-yes:hover { background: var(--pink-dark); }
    .cms-confirm-no { background: var(--surface-2); color: var(--ink-soft); }
    .cms-confirm-no:hover { background: var(--line); }

    .booking-tier-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(62px, 1fr));
        gap: 6px;
    }

    .booking-tier-field label {
        display: block;
        font-size: .6rem;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: 3px;
        letter-spacing: .02em;
    }

    .booking-tier-field[data-pax="1"] label { color: var(--pink-dark); }

    .booking-price-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .booking-price-input-wrap span {
        position: absolute;
        left: 7px;
        font-size: .7rem;
        color: var(--muted);
        font-weight: 700;
        pointer-events: none;
    }
    .booking-price-input {
        width: 100%;
        font-family: inherit;
        font-size: .74rem;
        font-weight: 700;
        color: var(--ink);
        padding: 6px 6px 6px 18px;
        border-radius: 8px;
        border: 1.5px solid var(--line-strong);
        background: var(--card);
        outline: none;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .booking-price-input:focus {
        border-color: var(--pink);
        box-shadow: var(--ring);
    }
    .booking-price-input.is-overridden {
        border-color: var(--pink);
        background: var(--pink-pale);
    }
    .booking-price-input.is-auto-filled {
        background: var(--green-light);
        border-color: var(--green);
    }
    .booking-price-input.is-dirty { border-color: var(--amber); }

    .booking-overridden-tag {
        font-size: .55rem;
        font-weight: 800;
        color: var(--pink-dark);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-top: 2px;
        display: block;
    }

    /* ---------- add-ons (aside) ---------- */
    .booking-addon-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid var(--line);
        transition: background .15s;
    }
    .booking-addon-row:last-child { border-bottom: none; }

    .booking-addon-name { font-size: .78rem; font-weight: 700; color: var(--ink); line-height: 1.3; }

    .booking-addon-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        position: relative;
    }
    .booking-addon-controls .booking-price-input-wrap { width: 92px; }
    .booking-addon-controls .booking-price-input { font-size: .74rem; padding: 6px 6px 6px 18px; }

    .booking-aside-empty {
        font-size: .74rem;
        color: var(--muted);
        text-align: center;
        padding: 18px 8px;
    }

    .cms-panel-foot {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid var(--line);
    }
</style>
@endsection

@section('content')
    @php
        $categoryLabels = ['solo' => 'Solo', 'bundle' => 'Bundle', 'packages' => 'Packages'];
        $serviceIcons = ['dino_adventure' => 'fa-dragon', 'rollerfever' => 'fa-bolt', 'field_of_rides' => 'fa-ferris-wheel'];

        $totalPackages = 0;
        $totalAddons = 0;
        $totalOverridesCount = 0;
        $overridesPerService = [];
        $packagesPerService = [];
        $addonsPerService = [];

        foreach ($packages as $svcCode => $svcPackages) {
            $totalPackages += count($svcPackages);
            $packagesPerService[$svcCode] = count($svcPackages);
            $pkgOverrideCount = collect($overrides['packages'][$svcCode] ?? [])->sum(fn ($tiers) => count($tiers));
            $addonOverrideCount = count($overrides['addons'][$svcCode] ?? []);
            $overridesPerService[$svcCode] = $pkgOverrideCount + $addonOverrideCount;
            $totalOverridesCount += $overridesPerService[$svcCode];
        }
        foreach ($addons as $svcCode => $svcAddons) {
            $totalAddons += count($svcAddons);
            $addonsPerService[$svcCode] = count($svcAddons);
        }
    @endphp

    <div class="cms-crumb">
        <a href="{{ route('cms.index') }}">Website Management</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Booking & Pricing</span>
    </div>

    <div class="cms-toolbar">
        <div class="cms-toolbar-main">
            <h1>Booking & Pricing</h1>
            <p>Edit package tier prices and add-on fees per service. Changes apply to the live booking page immediately.</p>
        </div>

        <div class="cms-toolbar-stats">
            <span class="cms-stat-chip"><i class="fa-solid fa-box-open"></i> <strong>{{ $totalPackages }}</strong>&nbsp;packages</span>
            <span class="cms-stat-chip"><i class="fa-solid fa-plus"></i> <strong>{{ $totalAddons }}</strong>&nbsp;add-ons</span>
            <span class="cms-stat-chip {{ $totalOverridesCount > 0 ? 'is-highlight' : '' }}">
                <i class="fa-solid fa-tag"></i> <strong>{{ $totalOverridesCount }}</strong>&nbsp;custom {{ Str::plural('price', $totalOverridesCount) }}
            </span>
        </div>

        <div class="cms-toolbar-actions">
            <span class="cms-dirty-flag" id="cmsDirtyFlag"><span class="cms-dirty-dot"></span> Unsaved changes</span>
            <button type="submit" form="bookingPricingForm" class="cms-btn-solid" id="cmsSaveTop">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="cms-alert cms-alert--success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="cms-alert cms-alert--error">There are errors in the form — please check the fields.</div>
    @endif

    <div class="booking-service-tabs" id="bookingServiceTabs" role="tablist">
        @foreach ($services as $svcCode => $svc)
            <button type="button" class="booking-service-tab {{ $loop->first ? 'is-active' : '' }}"
                data-service-tab="{{ $svcCode }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                <span class="svc-tab-icon"><i class="fa-solid {{ $serviceIcons[$svcCode] ?? 'fa-star' }}"></i></span>
                <span class="svc-tab-text">
                    {{ $svc['name'] }}
                    <small>{{ $svc['tagline'] }}</small>
                </span>
                @if (($overridesPerService[$svcCode] ?? 0) > 0)
                    <span class="svc-tab-badge">{{ $overridesPerService[$svcCode] }} custom</span>
                @endif
            </button>
        @endforeach
    </div>

    <form action="{{ route('cms.booking.pricing.update') }}" method="POST" id="bookingPricingForm">
        @csrf
        @method('PUT')

        @foreach ($services as $svcCode => $svc)
            <div class="booking-service-panel" data-service-panel="{{ $svcCode }}" role="tabpanel" {{ $loop->first ? '' : 'hidden' }}>

                <div class="booking-layout">
                    {{-- ---------------- MAIN: Packages / Tiers ---------------- --}}
                    <div class="cms-panel">
                        <div class="cms-panel-head">
                            <span class="cms-panel-title"><span class="cms-dot"></span> {{ $svc['name'] }} — Packages &amp; Tiers</span>
                            <div class="cms-panel-head-right">
                                <span class="cms-mini-chip">{{ $packagesPerService[$svcCode] ?? 0 }} {{ Str::plural('package', $packagesPerService[$svcCode] ?? 0) }}</span>
                                @if (($overridesPerService[$svcCode] ?? 0) > 0)
                                    <span class="cms-mini-chip is-flag">{{ $overridesPerService[$svcCode] }} custom</span>
                                @endif
                                <span class="cms-panel-hint">Leave a field as-is to keep it unchanged</span>
                            </div>
                        </div>

                        <div class="cms-panel-body">
                            @php
                                $servicePackages = $packages[$svcCode] ?? [];
                                $categoriesInService = collect($servicePackages)->pluck('category')->unique()->values()->all();
                            @endphp

                            @foreach ($categoriesInService as $cat)
                                @php
                                    $packagesInCat = collect($servicePackages)->filter(fn ($p) => $p['category'] === $cat);
                                @endphp
                                <p class="booking-category-label">
                                    {{ $categoryLabels[$cat] ?? ucfirst($cat) }}
                                    <span class="booking-category-count">{{ $packagesInCat->count() }} {{ Str::plural('package', $packagesInCat->count()) }}</span>
                                </p>

                                <div class="booking-package-grid">
                                    @foreach ($servicePackages as $pkgCode => $package)
                                        @continue($package['category'] !== $cat)
                                        @php
                                            $overriddenTiers = $overrides['packages'][$svcCode][$pkgCode] ?? [];
                                        @endphp
                                        <div class="booking-package-card" data-category="{{ $package['category'] }}">
                                            <div class="booking-package-head">
                                                <div>
                                                    <h3>{{ $package['name'] }}</h3>
                                                    <p>{{ $package['desc'] }}</p>
                                                </div>
                                                @if (count($overriddenTiers) > 0)
                                                    <button type="button" class="booking-reset-btn" data-reset-package
                                                        data-url="{{ route('cms.booking.pricing.reset-package', [$svcCode, $pkgCode]) }}"
                                                        data-confirm-label="{{ $package['name'] }}">
                                                        <i class="fa-solid fa-rotate-left"></i> Reset
                                                    </button>
                                                @endif
                                            </div>

                                            @if ($package['category'] === 'solo')
                                                <span class="booking-package-hint">
                                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                                    Edit 1 PAX — rest auto-fill
                                                </span>
                                            @endif

                                            <div class="booking-tier-grid">
                                                @foreach ($package['tiers'] as $pax => $price)
                                                    @php $isOverridden = isset($overriddenTiers[$pax]); @endphp
                                                    <div class="booking-tier-field" data-pax="{{ $pax }}">
                                                        <label for="tier_{{ $svcCode }}_{{ $pkgCode }}_{{ $pax }}">{{ $pax }} PAX</label>
                                                        <div class="booking-price-input-wrap">
                                                            <span>₱</span>
                                                            <input type="number" step="0.01" min="0"
                                                                name="tiers[{{ $svcCode }}][{{ $pkgCode }}][{{ $pax }}]"
                                                                id="tier_{{ $svcCode }}_{{ $pkgCode }}_{{ $pax }}"
                                                                value="{{ old('tiers.'.$svcCode.'.'.$pkgCode.'.'.$pax, $price) }}"
                                                                data-original="{{ $price }}"
                                                                class="booking-price-input {{ $isOverridden ? 'is-overridden' : '' }}">
                                                        </div>
                                                        @if ($isOverridden)
                                                            <span class="booking-overridden-tag">Custom</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ---------------- ASIDE: Add-ons ---------------- --}}
                    <div class="booking-aside">
                        @php $serviceAddons = $addons[$svcCode] ?? []; @endphp
                        <div class="cms-panel">
                            <div class="cms-panel-head">
                                <span class="cms-panel-title"><span class="cms-dot"></span> Add-ons</span>
                                <span class="cms-mini-chip">{{ count($serviceAddons) }} {{ Str::plural('item', count($serviceAddons)) }}</span>
                            </div>

                            <div class="cms-panel-body" style="padding-top: 4px; padding-bottom: 4px;">
                                @forelse ($serviceAddons as $addonCode => $addon)
                                    @php $isAddonOverridden = isset($overrides['addons'][$svcCode][$addonCode]); @endphp
                                    <div class="booking-addon-row">
                                        <span class="booking-addon-name">{{ $addon['name'] }}</span>
                                        <div class="booking-addon-controls">
                                            @if ($isAddonOverridden)
                                                <button type="button" class="booking-reset-btn" data-reset-addon
                                                    data-url="{{ route('cms.booking.pricing.reset-addon', [$svcCode, $addonCode]) }}"
                                                    data-confirm-label="{{ $addon['name'] }}">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            @endif
                                            <div class="booking-price-input-wrap">
                                                <span>₱</span>
                                                <input type="number" step="0.01" min="0"
                                                    name="addons[{{ $svcCode }}][{{ $addonCode }}]"
                                                    value="{{ old('addons.'.$svcCode.'.'.$addonCode, $addon['price']) }}"
                                                    data-original="{{ $addon['price'] }}"
                                                    class="booking-price-input {{ $isAddonOverridden ? 'is-overridden' : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="booking-aside-empty">No add-ons for this service yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endforeach

        <div class="cms-panel-foot">
            <button type="submit" class="cms-btn-solid" id="cmsSaveBottom">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ---------------------------------------------------------------
    // Service tab switching — delegated on document so it keeps working
    // even if this panel gets re-inserted into the page later (e.g. by
    // an AJAX/pjax-style admin nav that swaps #content without a full
    // reload, which previously meant this script block only ever ran
    // once and later clicks on the tabs did nothing).
    // ---------------------------------------------------------------
    document.addEventListener('click', function (e) {
        var tab = e.target.closest('#bookingServiceTabs [data-service-tab]');
        if (!tab) return;

        document.querySelectorAll('#bookingServiceTabs [data-service-tab]').forEach(function (t) {
            var active = t === tab;
            t.classList.toggle('is-active', active);
            t.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        var svc = tab.dataset.serviceTab;
        document.querySelectorAll('[data-service-panel]').forEach(function (panel) {
            panel.hidden = panel.dataset.servicePanel !== svc;
        });
    });

    // ---------------------------------------------------------------
    // Per-pax auto-scale for "solo" (per-guest) packages only, e.g.
    // Dino Adventure's walk-in passes or Field of Rides' per-head
    // tickets — these are always priced as (per-guest rate × pax), so
    // editing the 1 PAX field recalculates 2–10 PAX automatically.
    // "bundle" and "packages" categories (group promos, exclusive
    // party packages) have custom, non-linear group discounts and are
    // intentionally left untouched — each tier there is still edited
    // by hand. Delegated on 'input' so it survives any dynamic re-render.
    // ---------------------------------------------------------------
    document.addEventListener('input', function (e) {
        var input = e.target;
        if (!input.classList || !input.classList.contains('booking-price-input')) return;

        // mark dirty state relative to the server-rendered value
        var original = input.dataset.original;
        if (original !== undefined) {
            input.classList.toggle('is-dirty', String(parseFloat(original)) !== String(parseFloat(input.value || 0)));
        }
        markFormDirty();

        var field = input.closest('.booking-tier-field');
        var card = input.closest('.booking-package-card[data-category="solo"]');
        if (!field || !card) return;

        if (field.dataset.pax === '1') {
            var perGuest = parseFloat(input.value);
            if (isNaN(perGuest) || perGuest < 0) return;

            card.querySelectorAll('.booking-tier-field').forEach(function (f) {
                var pax = parseInt(f.dataset.pax, 10);
                if (!pax || pax === 1) return;
                var scaled = f.querySelector('.booking-price-input');
                if (!scaled) return;
                scaled.value = (perGuest * pax).toFixed(2);
                scaled.classList.add('is-auto-filled');
                markFormDirty();
            });
        } else {
            // a manual edit on a non-1-PAX field is now a deliberate
            // override again — drop the auto-filled highlight
            input.classList.remove('is-auto-filled');
        }
    });

    // ---------------------------------------------------------------
    // "Unsaved changes" indicator — gives visible feedback the moment
    // something is edited, so the Save button doesn't feel inert.
    // ---------------------------------------------------------------
    var dirtyFlag = document.getElementById('cmsDirtyFlag');
    var isDirty = false;
    function markFormDirty() {
        if (isDirty) return;
        isDirty = true;
        if (dirtyFlag) dirtyFlag.classList.add('is-visible');
    }
    window.addEventListener('beforeunload', function (e) {
        if (!isDirty) return;
        e.preventDefault();
        e.returnValue = '';
    });

    // show a spinner + disable both save buttons on submit so the click
    // always gives immediate feedback, even on a slow connection
    var form = document.getElementById('bookingPricingForm');
    if (form) {
        form.addEventListener('submit', function () {
            isDirty = false;
            ['cmsSaveTop', 'cmsSaveBottom'].forEach(function (id) {
                var btn = document.getElementById(id);
                if (!btn) return;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner"></i> Saving…';
            });
        });
    }

    // ---------------------------------------------------------------
    // Reset-to-default buttons (package tiers / add-ons).
    //
    // These used to call window.confirm(), which some browsers (and
    // most in-app/webview contexts) silently suppress after the first
    // call per page, or block entirely — so the button looked dead on
    // the second or third click. Replaced with a small inline popover
    // that always renders, is dismissible, and is delegated on
    // document so it keeps working after any dynamic content changes.
    // ---------------------------------------------------------------
    function closeAnyOpenPopover() {
        var open = document.querySelector('.cms-confirm-pop');
        if (open) open.remove();
    }

    document.addEventListener('click', function (e) {
        var resetBtn = e.target.closest('[data-reset-package], [data-reset-addon]');

        if (!resetBtn) {
            // clicking anywhere else closes an open popover, unless the
            // click was inside the popover itself
            if (!e.target.closest('.cms-confirm-pop')) closeAnyOpenPopover();
            return;
        }

        if (resetBtn.disabled) return;

        var already = resetBtn.querySelector(':scope > .cms-confirm-pop');
        closeAnyOpenPopover();
        if (already) return; // second click on the same button just closes it

        var label = resetBtn.dataset.confirmLabel || 'this item';
        var pop = document.createElement('div');
        pop.className = 'cms-confirm-pop';
        pop.innerHTML =
            '<p>Revert "' + label.replace(/</g, '&lt;') + '" to its default price(s)?</p>' +
            '<div class="cms-confirm-pop-actions">' +
                '<button type="button" class="cms-confirm-no">Cancel</button>' +
                '<button type="button" class="cms-confirm-yes">Reset</button>' +
            '</div>';
        resetBtn.appendChild(pop);

        pop.querySelector('.cms-confirm-no').addEventListener('click', function (ev) {
            ev.stopPropagation();
            pop.remove();
        });
        pop.querySelector('.cms-confirm-yes').addEventListener('click', function (ev) {
            ev.stopPropagation();
            resetBtn.disabled = true;
            submitReset(resetBtn.dataset.url);
        });
    });

    function submitReset(url) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        var meta = document.querySelector('meta[name="csrf-token"]');
        csrf.value = meta ? meta.content : '{{ csrf_token() }}';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    }
})();
</script>
@endpush