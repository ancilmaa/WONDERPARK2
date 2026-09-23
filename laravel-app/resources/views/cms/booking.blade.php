@extends('layouts.sidebar')

@section('title', 'Booking & Pricing')

@section('styles')
<style>
    /* ---------- top / crumb (matches cms.edit-section) ---------- */
    .cms-crumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .78rem;
        color: var(--muted);
    }
    .cms-crumb a { color: var(--muted); text-decoration: none; }
    .cms-crumb a:hover { color: var(--pink-dark); }
    .cms-crumb i { font-size: .6rem; color: var(--line-strong); }
    .cms-crumb .is-current { color: var(--ink-soft); font-weight: 600; }

    .cms-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .cms-top h1 { font-size: 1.5rem; font-weight: 800; color: var(--ink); margin: 2px 0 6px; }
    .cms-top p { font-size: .86rem; color: var(--muted); margin: 0; max-width: 60ch; }

    .cms-btn-ghost, .cms-btn-solid {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-family: inherit;
        font-size: .82rem;
        font-weight: 700;
        padding: 9px 16px;
        border-radius: 9px;
        cursor: pointer;
        white-space: nowrap;
        border: none;
    }
    .cms-btn-ghost { background: var(--card); color: var(--ink-soft); border: 1.5px solid var(--line-strong); }
    .cms-btn-ghost:hover { border-color: var(--pink); color: var(--pink-dark); }
    .cms-btn-solid { background: var(--pink); color: #fff; }
    .cms-btn-solid:hover { background: var(--pink-dark); }

    .cms-alert { padding: 12px 16px; border-radius: 10px; font-size: .85rem; font-weight: 600; margin-top: 16px; }
    .cms-alert--success { background: var(--green-light); color: var(--green); }
    .cms-alert--error { background: var(--pink-light); color: var(--pink-deep); }

    /* ---------- service tabs ---------- */
    .booking-service-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 22px;
        padding: 5px;
        background: var(--bg);
        border: 1px solid var(--line);
        border-radius: 14px;
    }
    .booking-service-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: 10px;
        border: 1.5px solid transparent;
        background: var(--card);
        color: var(--ink);
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
    }
    .booking-service-tab.is-active {
        background: var(--pink);
        color: #fff;
    }
    .booking-service-tab small {
        display: block;
        font-weight: 600;
        opacity: .75;
        font-size: .68rem;
    }

    .booking-service-panel[hidden] { display: none; }

    /* ---------- panel ---------- */
    .cms-panel {
        background: var(--card);
        border-radius: 16px;
        border: 1px solid var(--line);
        overflow: hidden;
        margin-top: 18px;
    }
    .cms-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 24px;
        border-bottom: 1px solid var(--line);
    }
    .cms-panel-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .05em;
        color: var(--ink);
        text-transform: uppercase;
    }
    .cms-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--pink); flex-shrink: 0;
        box-shadow: 0 0 0 3px rgba(255, 92, 133, .18);
    }
    .cms-panel-hint { font-size: .76rem; color: var(--muted); }
    .cms-panel-body { padding: 22px 24px; }

    /* ---------- package group ---------- */
    .booking-category-label {
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        margin: 22px 0 10px;
    }
    .booking-category-label:first-child { margin-top: 0; }

    .booking-package-card {
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 16px 18px;
        margin-bottom: 14px;
        background: var(--bg);
    }

    .booking-package-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .booking-package-head h3 {
        font-size: .92rem;
        font-weight: 700;
        color: var(--ink);
        margin: 0 0 3px;
    }
    .booking-package-head p {
        font-size: .76rem;
        color: var(--muted);
        margin: 0;
        max-width: 60ch;
    }

    .booking-package-hint {
        font-size: .7rem;
        font-weight: 600;
        color: var(--pink-dark);
        background: var(--pink-pale);
        border-radius: 7px;
        padding: 4px 9px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 12px;
    }

    .booking-reset-btn {
        font-family: inherit;
        font-size: .72rem;
        font-weight: 700;
        color: var(--pink-deep);
        background: var(--pink-pale);
        border: none;
        border-radius: 8px;
        padding: 6px 11px;
        cursor: pointer;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .booking-reset-btn:hover { background: var(--pink-light); }

    .booking-tier-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }

    .booking-tier-field label {
        display: block;
        font-size: .68rem;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: 4px;
    }

    .booking-tier-field[data-pax="1"] label {
        color: var(--pink-dark);
    }

    .booking-price-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .booking-price-input-wrap span {
        position: absolute;
        left: 10px;
        font-size: .8rem;
        color: var(--muted);
        font-weight: 700;
        pointer-events: none;
    }
    .booking-price-input {
        width: 100%;
        font-family: inherit;
        font-size: .84rem;
        font-weight: 700;
        color: var(--ink);
        padding: 8px 10px 8px 24px;
        border-radius: 8px;
        border: 1.5px solid var(--line-strong);
        background: var(--card);
        outline: none;
    }
    .booking-price-input:focus {
        border-color: var(--pink);
        box-shadow: 0 0 0 3px rgba(255, 92, 133, .12);
    }
    .booking-price-input.is-overridden {
        border-color: var(--pink);
        background: var(--pink-pale);
    }

    .booking-price-input.is-auto-filled {
        background: var(--green-light);
        border-color: var(--green);
    }

    .booking-overridden-tag {
        font-size: .6rem;
        font-weight: 800;
        color: var(--pink-dark);
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-top: 3px;
        display: block;
    }

    /* ---------- add-ons ---------- */
    .booking-addon-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 12px 0;
        border-bottom: 1px solid var(--line);
    }
    .booking-addon-row:last-child { border-bottom: none; }

    .booking-addon-name { font-size: .86rem; font-weight: 700; color: var(--ink); }

    .booking-addon-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .booking-addon-controls .booking-price-input-wrap { width: 130px; }

    .cms-panel-foot {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid var(--line);
    }
</style>
@endsection

@section('content')
    <div class="cms-crumb">
        <a href="{{ route('cms.index') }}">Website Management</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span class="is-current">Booking & Pricing</span>
    </div>

    <div class="cms-top">
        <div>
            <h1>Booking & Pricing</h1>
            <p>Edit package tier prices and add-on fees per service. Changes here apply to the live booking page immediately.</p>
        </div>
        <div>
            <button type="submit" form="bookingPricingForm" class="cms-btn-solid">
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

    <div class="booking-service-tabs" id="bookingServiceTabs">
        @foreach ($services as $svcCode => $svc)
            <button type="button" class="booking-service-tab {{ $loop->first ? 'is-active' : '' }}" data-service-tab="{{ $svcCode }}">
                {{ $svc['name'] }}
                <small>{{ $svc['tagline'] }}</small>
            </button>
        @endforeach
    </div>

    <form action="{{ route('cms.booking.pricing.update') }}" method="POST" id="bookingPricingForm">
        @csrf
        @method('PUT')

        @foreach ($services as $svcCode => $svc)
            <div class="booking-service-panel" data-service-panel="{{ $svcCode }}" {{ $loop->first ? '' : 'hidden' }}>

                {{-- ---------------- Packages / Tiers ---------------- --}}
                <div class="cms-panel">
                    <div class="cms-panel-head">
                        <span class="cms-panel-title"><span class="cms-dot"></span> {{ $svc['name'] }} — Packages &amp; Tiers</span>
                        <span class="cms-panel-hint">Prices are per pax tier. Leave a field as-is to keep it unchanged.</span>
                    </div>

                    <div class="cms-panel-body">
                        @php
                            $servicePackages = $packages[$svcCode] ?? [];
                            $categoriesInService = collect($servicePackages)->pluck('category')->unique()->values()->all();
                            $categoryLabels = ['solo' => 'Solo', 'bundle' => 'Bundle', 'packages' => 'Packages'];
                        @endphp

                        @foreach ($categoriesInService as $cat)
                            <p class="booking-category-label">{{ $categoryLabels[$cat] ?? ucfirst($cat) }}</p>

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
                                                data-url="{{ route('cms.booking.pricing.reset-package', [$svcCode, $pkgCode]) }}">
                                                <i class="fa-solid fa-rotate-left"></i> Reset to default
                                            </button>
                                        @endif
                                    </div>

                                    @if ($package['category'] === 'solo')
                                        <div class="booking-package-hint">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                            Edit the 1 PAX price — the rest auto-fill as a multiple of it
                                        </div>
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
                                                        class="booking-price-input {{ $isOverridden ? 'is-overridden' : '' }}">
                                                </div>
                                                @if ($isOverridden)
                                                    <span class="booking-overridden-tag">Custom price</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                {{-- ---------------- Add-ons ---------------- --}}
                @php $serviceAddons = $addons[$svcCode] ?? []; @endphp
                @if (count($serviceAddons) > 0)
                    <div class="cms-panel">
                        <div class="cms-panel-head">
                            <span class="cms-panel-title"><span class="cms-dot"></span> {{ $svc['name'] }} — Add-ons</span>
                            <span class="cms-panel-hint">Flat fees added on top of a package or walk-in pass</span>
                        </div>

                        <div class="cms-panel-body">
                            @foreach ($serviceAddons as $addonCode => $addon)
                                @php $isAddonOverridden = isset($overrides['addons'][$svcCode][$addonCode]); @endphp
                                <div class="booking-addon-row">
                                    <span class="booking-addon-name">{{ $addon['name'] }}</span>
                                    <div class="booking-addon-controls">
                                        @if ($isAddonOverridden)
                                            <button type="button" class="booking-reset-btn" data-reset-addon
                                                data-url="{{ route('cms.booking.pricing.reset-addon', [$svcCode, $addonCode]) }}">
                                                <i class="fa-solid fa-rotate-left"></i> Reset
                                            </button>
                                        @endif
                                        <div class="booking-price-input-wrap">
                                            <span>₱</span>
                                            <input type="number" step="0.01" min="0"
                                                name="addons[{{ $svcCode }}][{{ $addonCode }}]"
                                                value="{{ old('addons.'.$svcCode.'.'.$addonCode, $addon['price']) }}"
                                                class="booking-price-input {{ $isAddonOverridden ? 'is-overridden' : '' }}">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        @endforeach

        <div class="cms-panel-foot">
            <button type="submit" class="cms-btn-solid">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    // Service tab switching
    document.querySelectorAll('#bookingServiceTabs [data-service-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('#bookingServiceTabs [data-service-tab]').forEach(function (t) {
                t.classList.toggle('is-active', t === tab);
            });
            var svc = tab.dataset.serviceTab;
            document.querySelectorAll('[data-service-panel]').forEach(function (panel) {
                panel.hidden = panel.dataset.servicePanel !== svc;
            });
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
    // by hand.
    // ---------------------------------------------------------------
    document.querySelectorAll('.booking-package-card[data-category="solo"]').forEach(function (card) {
        var baseField = card.querySelector('.booking-tier-field[data-pax="1"] .booking-price-input');
        if (!baseField) return;

        function recalculate() {
            var perGuest = parseFloat(baseField.value);
            if (isNaN(perGuest) || perGuest < 0) return;

            card.querySelectorAll('.booking-tier-field').forEach(function (field) {
                var pax = parseInt(field.dataset.pax, 10);
                if (!pax || pax === 1) return;

                var input = field.querySelector('.booking-price-input');
                if (!input) return;

                input.value = (perGuest * pax).toFixed(2);
                input.classList.add('is-auto-filled');
            });
        }

        baseField.addEventListener('input', recalculate);

        // If someone manually edits a non-1-PAX field afterwards, drop
        // the "auto-filled" highlight on that one field only — it's now
        // a deliberate manual override again.
        card.querySelectorAll('.booking-tier-field:not([data-pax="1"]) .booking-price-input').forEach(function (input) {
            input.addEventListener('input', function () {
                input.classList.remove('is-auto-filled');
            });
        });
    });

    // Reset-to-default buttons (package tiers / add-ons) — plain POST
    // form submit (the routes are registered as POST, not DELETE, despite
    // the "DELETE /cms/booking/..." comment in BookingCmsController).
    function submitReset(url) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]')
            ? document.querySelector('meta[name="csrf-token"]').content
            : '{{ csrf_token() }}';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    }

    document.querySelectorAll('[data-reset-package], [data-reset-addon]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Revert this back to the default price(s)?')) return;
            submitReset(btn.dataset.url);
        });
    });
})();
</script>
@endpush