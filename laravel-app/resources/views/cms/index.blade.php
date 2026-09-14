@extends('layouts.sidebar')

@section('title', 'Website CMS')

@section('styles')
<style>
    .cms-intro {
        color: var(--ink-soft);
        font-size: .9rem;
        margin-top: 4px;
        max-width: 640px;
    }

    .cms-shell {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 28px;
        margin-top: 24px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .cms-shell {
            grid-template-columns: 1fr;
        }
    }

    .cms-panel {
        background: var(--card);
        border-radius: 16px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .cms-panel-header {
        padding: 18px 20px 14px;
        border-bottom: 1px solid var(--line);
    }

    .cms-panel-header h2 {
        font-size: .95rem;
        font-weight: 700;
        color: var(--ink);
    }

    .cms-panel-header p {
        font-size: .78rem;
        color: var(--ink-soft);
        margin-top: 2px;
    }

    .cms-content-row {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--line);
    }

    .cms-content-row:last-child {
        border-bottom: none;
    }

    .cms-content-icon {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        background: var(--pink-light);
        color: var(--pink-deep);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
    }

    .cms-content-body {
        flex: 1;
        min-width: 0;
    }

    .cms-content-body h3 {
        font-size: .88rem;
        font-weight: 700;
        color: var(--ink);
    }

    .cms-content-body p {
        font-size: .76rem;
        color: var(--ink-soft);
        margin-top: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cms-content-edit {
        flex-shrink: 0;
        font-size: .78rem;
        font-weight: 700;
        color: var(--pink-dark);
        padding: 6px 12px;
        border-radius: 8px;
        background: var(--pink-pale);
    }

    .cms-content-edit:hover {
        background: var(--pink-light);
    }

    .cms-section-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 12px;
    }

    .cms-collection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }

    .cms-collection-card {
        background: var(--card);
        border-radius: 16px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        transition: box-shadow .15s ease, transform .15s ease;
    }

    .cms-collection-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }

    .cms-collection-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .cms-collection-icon {
        width: 42px;
        height: 42px;
        border-radius: 11px;
        background: var(--pink-light);
        color: var(--pink-deep);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .cms-collection-count {
        font-size: .72rem;
        font-weight: 700;
        color: var(--pink-dark);
        background: var(--pink-pale);
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .cms-collection-card h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--ink);
    }

    .cms-collection-card p {
        font-size: .8rem;
        color: var(--ink-soft);
        line-height: 1.45;
        flex: 1;
    }

    .cms-collection-card a.btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 16px;
        border-radius: 9px;
        background: var(--pink);
        color: #fff;
        font-weight: 600;
        font-size: .82rem;
    }

    .cms-collection-card a.btn:hover {
        background: var(--pink-dark);
    }
</style>
@endsection

@section('content')
    <h1 style="font-size:1.5rem;font-weight:800;color:var(--ink);">Website CMS</h1>
    <p class="cms-intro">Manage everything that appears on Wonder Park's public landing page, from section copy to pricing cards.</p>

    @if (session('success'))
        <div style="margin-top:16px;padding:11px 16px;background:var(--green-light);color:var(--green);border-radius:10px;font-size:.85rem;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif

    <div class="cms-shell">
        <div class="cms-panel">
            <div class="cms-panel-header">
                <h2>Site Content</h2>
                <p>Text blocks and copy sections</p>
            </div>

            <div class="cms-content-row">
                <div class="cms-content-icon"><i class="fa-solid fa-house"></i></div>
                <div class="cms-content-body">
                    <h3>Hero Section</h3>
                    <p>Headline, description, price teaser, background video</p>
                </div>
                <a href="{{ route('cms.section.edit', 'hero') }}" class="cms-content-edit">Edit</a>
            </div>

            <div class="cms-content-row">
                <div class="cms-content-icon"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="cms-content-body">
                    <h3>Plan Your Day</h3>
                    <p>The "full day out" split section</p>
                </div>
                <a href="{{ route('cms.section.edit', 'split') }}" class="cms-content-edit">Edit</a>
            </div>

            <div class="cms-content-row">
                <div class="cms-content-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div class="cms-content-body">
                    <h3>Contact Info</h3>
                    <p>Address, hours, zone list</p>
                </div>
                <a href="{{ route('cms.section.edit', 'contact') }}" class="cms-content-edit">Edit</a>
            </div>

            <div class="cms-content-row">
                <div class="cms-content-icon"><i class="fa-solid fa-shoe-prints"></i></div>
                <div class="cms-content-body">
                    <h3>Footer</h3>
                    <p>Tagline shown at the bottom of the page</p>
                </div>
                <a href="{{ route('cms.section.edit', 'footer') }}" class="cms-content-edit">Edit</a>
            </div>
        </div>

        <div>
            <div class="cms-section-label">Card Collections</div>
            <div class="cms-collection-grid">
                <div class="cms-collection-card">
                    <div class="cms-collection-top">
                        <div class="cms-collection-icon"><i class="fa-solid fa-ticket"></i></div>
                        <span class="cms-collection-count">{{ $cardCounts['pass'] }} passes</span>
                    </div>
                    <h3>Day Passes</h3>
                    <p>Dino Adventure, Roller Fever, and Field of Rides pricing cards with price-list modals.</p>
                    <a href="{{ route('cms.cards', 'pass') }}" class="btn">Manage</a>
                </div>

                <div class="cms-collection-card">
                    <div class="cms-collection-top">
                        <div class="cms-collection-icon"><i class="fa-solid fa-ferris-wheel"></i></div>
                        <span class="cms-collection-count">{{ $cardCounts['attraction'] }} rides</span>
                    </div>
                    <h3>Attractions</h3>
                    <p>The ride cards shown in the Attractions carousel, including policies.</p>
                    <a href="{{ route('cms.cards', 'attraction') }}" class="btn">Manage</a>
                </div>

                <div class="cms-collection-card">
                    <div class="cms-collection-top">
                        <div class="cms-collection-icon"><i class="fa-solid fa-concierge-bell"></i></div>
                        <span class="cms-collection-count">{{ $cardCounts['service'] }} services</span>
                    </div>
                    <h3>Guest Services</h3>
                    <p>Party packages, reservations, booking channels, payment, and the snack bar.</p>
                    <a href="{{ route('cms.cards', 'service') }}" class="btn">Manage</a>
                </div>

                <div class="cms-collection-card">
                    <div class="cms-collection-top">
                        <div class="cms-collection-icon"><i class="fa-solid fa-list-ol"></i></div>
                        <span class="cms-collection-count">{{ $cardCounts['step'] }} steps</span>
                    </div>
                    <h3>How It Works</h3>
                    <p>The 3-step Book → Scan → Ride sequence.</p>
                    <a href="{{ route('cms.cards', 'step') }}" class="btn">Manage</a>
                </div>
            </div>
        </div>
    </div>
@endsection