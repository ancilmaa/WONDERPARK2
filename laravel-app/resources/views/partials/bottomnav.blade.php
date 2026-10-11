{{--
    resources/views/partials/bottomnav.blade.php
    Desktop (>=960px): admin-style dark sidebar (collapsible rail, remembered in
    localStorage under the same key the admin sidebar uses).
    Mobile: bottom tab bar.

    Tab order: Booking -> My Bookings -> Account -> Activity.
    Waiver is not a nav item — it's a mandatory gate shown right after
    login/register (see WaiverController@show).
--}}
@php
    // Who is signed in? Controllers that pass $user win; otherwise fall back to the session.
    $navUser = $user ?? (auth()->check() ? auth()->user() : null);
    if (! $navUser && session('user_id')) {
        $navUser = \App\Models\User::find(session('user_id'));
    }
    $navName    = trim((string) ($navUser->name ?? '')) ?: (trim((string) ($navUser->fullname ?? '')) ?: 'Guest');
    $navInitial = strtoupper(mb_substr($navName, 0, 1));
    $navAvatars = [
        'rose' => ['#FF5C85', '#B82850'],
        'gold' => ['#FFC948', '#C77E0E'],
        'teal' => ['#12B5A6', '#0C8B80'],
        'ink'  => ['#3B3350', '#171126'],
        'blue' => ['#5B8CFF', '#2E52C7'],
    ];
    [$navA, $navB] = $navAvatars[$navUser->avatar_theme ?? 'rose'] ?? $navAvatars['rose'];
    $navPhoto = ! empty($navUser->avatar_path) ? asset('storage/' . $navUser->avatar_path) : null;
@endphp
<script>
    /* apply the saved sidebar state before the nav paints (same key as the admin sidebar) */
    (function () {
        var KEY = 'wp.sidebar.collapsed', root = document.documentElement;
        try { if (localStorage.getItem(KEY) === '1') root.classList.add('sb-collapsed'); } catch (e) {}
        document.addEventListener('DOMContentLoaded', function () {
            var btn = document.getElementById('navToggle');
            if (!btn) return;
            function sync() { btn.setAttribute('aria-expanded', root.classList.contains('sb-collapsed') ? 'false' : 'true'); }
            sync();
            btn.addEventListener('click', function () {
                root.classList.toggle('sb-collapsed');
                try { localStorage.setItem(KEY, root.classList.contains('sb-collapsed') ? '1' : '0'); } catch (e) {}
                sync();
            });
        });
    })();
</script>
<nav class="bottomnav" aria-label="Main menu">
    <div class="nav-brand">
        <button type="button" class="nav-toggle" id="navToggle" aria-label="Collapse or expand menu" aria-expanded="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="18" x2="20" y2="18"></line></svg>
        </button>
        <div class="brand-mark"><img src="{{ asset('images/wonderpark1logo.png') }}" alt="Wonder Park"></div>
        <div class="brand-text">
            <span class="brand-name">WonderPark</span>
            <span class="brand-sub">Lipa Branch &middot; Customer</span>
        </div>
    </div>

    <div class="nav-list">
        <span class="nav-section-label only-desktop">My Visit</span>

        <a href="{{ route('user.booking') }}" title="Booking"
           class="bn-item {{ request()->routeIs('user.booking', 'user.booking.store') ? 'active' : '' }}">
            <span class="ic3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path></svg>
            </span>
            <span class="bn-label">Booking</span>
        </a>

        <a href="{{ route('user.bookings') }}" title="My Bookings"
           class="bn-item {{ request()->routeIs('user.bookings*') ? 'active' : '' }}">
            <span class="ic3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><path d="M2 13h20"></path></svg>
            </span>
            <span class="bn-label">My Bookings</span>
        </a>

        <a href="{{ route('user.dashboard') }}" title="Account"
           class="bn-item {{ request()->routeIs('user.account') || request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <span class="ic3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 4-7 8-7s8 3 8 7"></path></svg>
            </span>
            <span class="bn-label">Account</span>
        </a>

        <a href="{{ route('user.activity') }}" title="Activity"
           class="bn-item {{ request()->routeIs('user.activity') ? 'active' : '' }}">
            <span class="ic3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
            </span>
            <span class="bn-label">Activity</span>
        </a>
    </div>

    <div class="nav-foot only-desktop">
        <div class="nav-foot-user">
            <span class="nav-foot-avatar" style="--u-avatar-a:{{ $navA }};--u-avatar-b:{{ $navB }};">
                @if ($navPhoto)
                    <img src="{{ $navPhoto }}" alt="">
                @else
                    {{ $navInitial }}
                @endif
            </span>
            <span class="nav-foot-info">
                <span class="nav-foot-name">{{ $navName }}</span>
                <span class="nav-foot-role">Customer</span>
            </span>
            <form method="POST" action="{{ route('logout') }}" class="nav-logout-form">
                @csrf
                <button type="submit" class="nav-foot-logout" title="Log out" aria-label="Log out">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>
                </button>
            </form>
        </div>
    </div>
</nav>
