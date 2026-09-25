@extends('layouts.sidebar')

@section('title', 'Website CMS')

@section('styles')
<style>
    /* ---------- Top bar ---------- */
    .cms-topbar {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--line);
    }

    .cms-crumbs {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .82rem;
        color: var(--muted);
        white-space: nowrap;
    }

    .cms-crumbs span.sep {
        color: var(--line);
    }

    .cms-crumbs strong {
        color: var(--ink);
        font-weight: 700;
    }

    .cms-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: .76rem;
        font-weight: 700;
        color: var(--green);
        background: var(--green-light);
        padding: 5px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .cms-status i {
        font-size: .5rem;
    }

    .cms-search {
        flex: 1;
        min-width: 180px;
        max-width: 320px;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 14px;
        border-radius: 10px;
        border: 1px solid var(--line);
        background: var(--card);
        color: var(--muted);
        font-size: .82rem;
    }

    .cms-search input {
        border: 0;
        outline: 0;
        background: transparent;
        width: 100%;
        font-size: .82rem;
        color: var(--ink);
    }

    .cms-topbar-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .cms-btn-ghost,
    .cms-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: .82rem;
        font-weight: 700;
        white-space: nowrap;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .cms-btn-ghost {
        background: var(--card);
        border-color: var(--line);
        color: var(--ink);
    }

    .cms-btn-ghost:hover {
        background: var(--pink-pale);
        border-color: var(--pink-light);
        color: var(--pink-dark);
    }

    .cms-btn-primary {
        background: var(--pink);
        color: #fff;
    }

    .cms-btn-primary:hover {
        background: var(--pink-dark);
    }

    /* ---------- Page heading ---------- */
    .cms-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-top: 26px;
    }

    .cms-head h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--ink);
        letter-spacing: -.01em;
    }

    .cms-intro {
        color: var(--ink-soft);
        font-size: .9rem;
        margin-top: 6px;
        max-width: 560px;
        line-height: 1.5;
    }

    .cms-head-meta {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: .8rem;
        color: var(--muted);
        white-space: nowrap;
    }

    .cms-head-meta b {
        color: var(--green);
    }

    /* ---------- Shell ---------- */
    .cms-shell {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 30px;
        margin-top: 26px;
        align-items: start;
    }

    @media (max-width: 1000px) {
        .cms-shell {
            grid-template-columns: 1fr;
        }
    }

    /* ---------- Site content panel ---------- */
    .cms-panel {
        background: var(--card);
        border-radius: 18px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        padding: 20px;
        margin-top: 57px;
    }

    .cms-panel-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--line);
    }

    .cms-panel-header h2 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--ink);
    }

    .cms-panel-header p {
        font-size: .78rem;
        color: var(--ink-soft);
        margin-top: 3px;
    }

    .cms-chip {
        font-size: .72rem;
        font-weight: 700;
        color: var(--ink-soft);
        background: var(--pink-pale);
        padding: 4px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .cms-content-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 16px 0;
    }

    .cms-content-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 13px;
        border: 1px solid var(--line);
        border-radius: 13px;
        background: var(--card);
        transition: border-color .15s ease, background .15s ease;
    }

    .cms-content-row:hover,
    .cms-content-row.is-active {
        border-color: var(--pink-light);
        background: var(--pink-pale);
    }

    .cms-content-icon {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
        border-radius: 10px;
        background: var(--pink-light);
        color: var(--pink-deep);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
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
        font-size: .75rem;
        color: var(--ink-soft);
        margin-top: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cms-content-edit {
        flex-shrink: 0;
        font-size: .76rem;
        font-weight: 700;
        color: var(--ink);
        padding: 6px 13px;
        border-radius: 8px;
        border: 1px solid var(--line);
        background: var(--card);
    }

    .cms-content-edit:hover {
        background: var(--pink);
        border-color: var(--pink);
        color: #fff;
    }

    .cms-panel-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 14px;
        border-top: 1px solid var(--line);
        font-size: .76rem;
        color: var(--muted);
    }

    .cms-panel-foot span.ok {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--green);
        font-weight: 700;
    }

    /* ---------- Collections ---------- */
    .cms-collections-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .cms-section-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .cms-collections-head h2 {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--ink);
        margin-top: 4px;
    }

    .cms-collections-hint {
        font-size: .78rem;
        color: var(--muted);
    }

    .cms-collection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(238px, 1fr));
        gap: 18px;
        align-items: stretch;
    }

    .cms-collection-card {
        position: relative;
        background: var(--card);
        border-radius: 18px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-sm);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
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
        width: 44px;
        height: 44px;
        border-radius: 13px;
        background: var(--pink-light);
        color: var(--pink-deep);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }

    .cms-collection-meta {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .cms-collection-count {
        font-size: .72rem;
        font-weight: 700;
        color: var(--pink-dark);
        background: var(--pink-pale);
        padding: 4px 11px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .cms-collection-delete {
        border: 0;
        background: transparent;
        color: var(--muted);
        font-size: .78rem;
        padding: 4px 6px;
        border-radius: 7px;
        cursor: pointer;
        line-height: 1;
    }

    .cms-collection-delete:hover {
        background: var(--pink-pale);
        color: var(--pink-dark);
    }

    .cms-collection-card h3 {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--ink);
    }

    .cms-collection-card p {
        font-size: .82rem;
        color: var(--ink-soft);
        line-height: 1.55;
        flex: 1;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--line);
    }

    .cms-collection-card a.btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 11px 16px;
        border-radius: 10px;
        background: var(--pink);
        color: #fff;
        font-weight: 700;
        font-size: .85rem;
    }

    .cms-collection-card a.btn:hover {
        background: var(--pink-dark);
    }

    /* ---------- Add collection tile ---------- */
    .cms-collection-add {
        border: 2px dashed var(--line);
        border-radius: 18px;
        background: transparent;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 6px;
        padding: 24px;
        min-height: 190px;
        color: var(--muted);
        cursor: pointer;
        font-family: inherit;
        transition: border-color .15s ease, background .15s ease;
    }

    .cms-collection-add:hover {
        border-color: var(--pink-light);
        background: var(--pink-pale);
    }

    .cms-collection-add .plus {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        background: var(--pink-pale);
        color: var(--pink-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
    }

    .cms-collection-add:hover .plus {
        background: var(--pink);
        color: #fff;
    }

    .cms-collection-add h3 {
        font-size: .92rem;
        font-weight: 700;
        color: var(--ink);
    }

    .cms-collection-add p {
        font-size: .76rem;
        line-height: 1.45;
        max-width: 170px;
    }

    /* ---------- Flash + errors ---------- */
    .cms-flash,
    .cms-error {
        margin-top: 18px;
        padding: 11px 16px;
        border-radius: 10px;
        font-size: .85rem;
        font-weight: 600;
    }

    .cms-flash {
        background: var(--green-light);
        color: var(--green);
    }

    .cms-error {
        background: var(--pink-pale);
        color: var(--pink-dark);
    }

    .cms-error ul {
        margin: 0;
        padding-left: 18px;
    }

    /* ---------- Modal ---------- */
    .cms-modal {
        position: fixed;
        inset: 0;
        z-index: 120;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(17, 17, 24, .45);
    }

    .cms-modal.is-open {
        display: flex;
    }

    .cms-modal-box {
        width: 100%;
        max-width: 460px;
        background: var(--card);
        border-radius: 18px;
        border: 1px solid var(--line);
        box-shadow: var(--shadow-md);
        padding: 24px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .cms-modal-box h2 {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--ink);
    }

    .cms-modal-box .sub {
        font-size: .82rem;
        color: var(--ink-soft);
        margin-top: 4px;
        margin-bottom: 18px;
    }

    .cms-field {
        margin-bottom: 14px;
    }

    .cms-field label {
        display: block;
        font-size: .78rem;
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 6px;
    }

    .cms-field input,
    .cms-field textarea,
    .cms-field select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid var(--line);
        background: var(--card);
        color: var(--ink);
        font-size: .85rem;
        font-family: inherit;
        outline: none;
    }

    .cms-field input:focus,
    .cms-field textarea:focus,
    .cms-field select:focus {
        border-color: var(--pink);
    }

    .cms-field textarea {
        resize: vertical;
        min-height: 74px;
    }

    .cms-field .hint {
        font-size: .72rem;
        color: var(--muted);
        margin-top: 5px;
    }

    .cms-field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .cms-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
    <div class="cms-topbar">
        <div>
            <h1>Website CMS</h1>
        </div>
        <div class="cms-topbar-actions">
            <a href="{{ url('/') }}" target="_blank" class="cms-btn-ghost">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> View public site
            </a>
            <button type="button" class="cms-btn-primary">
                <i class="fa-solid fa-cloud-arrow-up"></i> Publish sync
            </button>
        </div>
    </div>

    <div class="cms-head">
        
        <div class="cms-head-meta">
            <span>Auto-save: <b>Active</b></span>
        </div>
    </div>

    @if (session('success'))
        <div class="cms-flash">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="cms-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cms-shell">
        <div class="cms-panel">
            <div class="cms-panel-header">
                <div>
                    <h2>Site content</h2>
                    <p>Text blocks and copy sections</p>
                </div>
                <span class="cms-chip">4 blocks</span>
            </div>

            <div class="cms-content-list">
                <div class="cms-content-row is-active">
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

            <div class="cms-panel-foot">
                <span>All 4 sections in sync</span>
                <span class="ok"><i class="fa-solid fa-circle-check"></i> Healthy</span>
            </div>
        </div>

        <div>
            <div class="cms-collections-head">
                <div>
                    <div class="cms-section-label">Collections</div>
                    <h2>Card collections</h2>
                </div>
            </div>

            <div class="cms-collection-grid">
                @foreach ($collections as $collection)
                    <div class="cms-collection-card">
                        <div class="cms-collection-top">
                            <div class="cms-collection-icon"><i class="{{ $collection->icon }}"></i></div>
                            <div class="cms-collection-meta">
                                <span class="cms-collection-count">
                                    {{ $cardCounts[$collection->slug] ?? 0 }} {{ $collection->unit_label }}
                                </span>
                                @unless ($collection->is_system)
                                    <form method="POST"
                                          action="{{ route('cms.collections.destroy', $collection->slug) }}"
                                          onsubmit="return confirm('Delete the {{ $collection->title }} collection?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cms-collection-delete" title="Delete collection">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                        <h3>{{ $collection->title }}</h3>
                        <p>{{ $collection->description }}</p>
                        <a href="{{ route('cms.cards', $collection->slug) }}" class="btn">Manage</a>
                    </div>
                @endforeach

                <button type="button" class="cms-collection-add" data-open-collection-modal>
                    <div class="plus"><i class="fa-solid fa-plus"></i></div>
                    <h3>Add collection</h3>
                    <p>Create an interactive showcase section</p>
                </button>
            </div>
        </div>
    </div>

    {{-- Add collection modal --}}
    <div class="cms-modal {{ $errors->any() && old('title') ? 'is-open' : '' }}" id="collectionModal">
        <div class="cms-modal-box">
            <h2>New collection</h2>
            <p class="sub">Groups a set of cards you can manage together, like Day Passes or Attractions.</p>

            <form method="POST" action="{{ route('cms.collections.store') }}">
                @csrf

                <div class="cms-field">
                    <label for="collection-title">Name</label>
                    <input type="text" id="collection-title" name="title" maxlength="60" required
                           value="{{ old('title') }}" placeholder="Seasonal Events">
                </div>

                <div class="cms-field">
                    <label for="collection-description">Description</label>
                    <textarea id="collection-description" name="description" maxlength="200" required
                              placeholder="What this group of cards covers.">{{ old('description') }}</textarea>
                    <div class="hint">Shown on this dashboard only, not on the public site.</div>
                </div>

                <div class="cms-field-row">
                    <div class="cms-field">
                        <label for="collection-unit">Count label</label>
                        <input type="text" id="collection-unit" name="unit_label" maxlength="20" required
                               value="{{ old('unit_label', 'items') }}" placeholder="events">
                        <div class="hint">Used in the badge: "4 events".</div>
                    </div>

                    <div class="cms-field">
                        <label for="collection-icon">Icon</label>
                        <select id="collection-icon" name="icon">
                            <option value="fa-solid fa-layer-group">Layers</option>
                            <option value="fa-solid fa-calendar-star">Event</option>
                            <option value="fa-solid fa-gift">Gift</option>
                            <option value="fa-solid fa-utensils">Food</option>
                            <option value="fa-solid fa-camera">Photo</option>
                            <option value="fa-solid fa-star">Star</option>
                            <option value="fa-solid fa-map">Map</option>
                        </select>
                    </div>
                </div>

                <div class="cms-modal-actions">
                    <button type="button" class="cms-btn-ghost" data-close-collection-modal>Cancel</button>
                    <button type="submit" class="cms-btn-primary">Create collection</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var modal = document.getElementById('collectionModal');
            if (!modal) return;

            function openModal() {
                modal.classList.add('is-open');
                var first = modal.querySelector('input[name="title"]');
                if (first) first.focus();
            }

            function closeModal() {
                modal.classList.remove('is-open');
            }

            document.querySelectorAll('[data-open-collection-modal]').forEach(function (el) {
                el.addEventListener('click', openModal);
            });

            document.querySelectorAll('[data-close-collection-modal]').forEach(function (el) {
                el.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeModal();
            });
        })();
    </script>
@endsection