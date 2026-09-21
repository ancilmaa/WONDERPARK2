{{--
    resources/views/user/dashboard.blade.php
    Route: GET  /app/dashboard  ->  user.dashboard
           POST /app/account    ->  user.account.update
    Controller: App\Http\Controllers\User\DashboardController@index / update

    Expects: $user (App\Models\User), $upcomingCount (int)
--}}
@extends('layouts.user')

@section('title', 'Account')
@section('page-title', 'Account & Profile')
@section('page-subtitle', 'Manage your WonderPark account')

@section('body-class', 'page-uniform')

@section('content')

    @php
        $avatarChoices = [
            'rose'   => ['#FF5C85', '#B82850'],
            'gold'   => ['#FFC948', '#C77E0E'],
            'teal'   => ['#12B5A6', '#0C8B80'],
            'ink'    => ['#3B3350', '#171126'],
            'blue'   => ['#5B8CFF', '#2E52C7'],
        ];
        $currentAvatar = $user->avatar_theme ?? 'rose';
        [$avatarA, $avatarB] = $avatarChoices[$currentAvatar] ?? $avatarChoices['rose'];
        $hasPhoto = !empty($user->avatar_path);
        $avatarVersion = optional($user->updated_at)->timestamp ?? time();
        $isVerified = !empty($user->email_verified_at);
        $isActive = ($user->status ?? 'active') === 'active';
        $memberSince = optional($user->created_at)->format('M Y');
    @endphp

    {{-- Gradient hero banner --}}
    <div class="ap-hero">
        @if ($isVerified)
            <span class="ap-hero-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><path d="M20 6L9 17l-5-5"></path></svg>
                Verified Customer
            </span>
        @endif

        <div class="ap-hero-row">
            @if ($hasPhoto)
                <img src="{{ asset('storage/' . $user->avatar_path) }}?v={{ $avatarVersion }}"
                     alt="Profile photo"
                     class="ap-hero-avatar"
                     id="avatarPreviewImg"
                     style="object-fit:cover;">
            @else
                <div class="ap-hero-avatar" id="avatarPreviewFallback">
                    {{ strtoupper(substr($user->name ?? 'G', 0, 1)) }}
                </div>
            @endif

            <div style="flex:1;min-width:180px;">
                <h4 style="margin:0 0 4px;color:#fff;font-size:19px;">Hello, {{ $user->name ?? 'Guest' }} 👋</h4>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span class="ap-hero-badge">Customer Account</span>
                    @if ($memberSince)
                        <span style="font-size:11.5px;color:rgba(255,255,255,.85);">Member since {{ $memberSince }}</span>
                    @endif
                </div>
            </div>

            <div class="ap-hero-stats">
                <div class="ap-hero-stat">
                    <div class="ap-hero-stat-label">Bookings</div>
                    <div class="ap-hero-stat-value">{{ $upcomingCount }} Upcoming</div>
                </div>
                <div class="ap-hero-stat">
                    <div class="ap-hero-stat-label">Status</div>
                    <div class="ap-hero-stat-value" style="display:flex;align-items:center;gap:5px;">
                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $isActive ? '#12B5A6' : '#FF5C85' }};display:inline-block;"></span>
                        {{ ucfirst($user->status ?? 'active') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="u-grid-2" style="margin-top:16px;">

        {{-- Personal Information --}}
        <div class="u-card" style="margin-top:0;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                <div>
                    <h4 style="margin:0 0 2px;">Personal Information</h4>
                    <p style="margin:0;font-size:12px;color:var(--muted);">Update your display name and visual theme preferences.</p>
                </div>
                <span class="rb-badge rb-badge-neutral">Profile Data</span>
            </div>

            <form method="POST" action="{{ route('user.account.update') }}" enctype="multipart/form-data" id="accountForm" style="margin-top:16px;">
                @csrf
                <input type="hidden" name="avatar_theme" value="{{ $currentAvatar }}" id="avatarThemeField">

                <p class="u-field-label" style="margin-top:0;">Display name</p>
                <input type="text" name="name" class="u-input" id="displayNameInput" value="{{ old('name', $user->name ?? '') }}" maxlength="60" required>
                <p style="font-size:11px;color:var(--muted);margin:-8px 0 16px;">This name appears on digital tickets, park waivers, and customer service records.</p>

                <p class="u-field-label">Profile photo</p>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap;">
                    <label for="avatarPhotoInput" class="u-btn ghost" style="width:auto;display:inline-block;padding:9px 16px;font-size:12px;cursor:pointer;margin:0;">
                        <span id="avatarPhotoLabel">Change photo</span>
                    </label>
                    <input type="file" id="avatarPhotoInput" name="avatar_photo" accept="image/png,image/jpeg,image/webp"
                           form="accountForm" style="display:none;">
                    <span style="font-size:11.5px;color:var(--muted);">JPG, PNG, or WEBP. Max 2MB.</span>
                </div>

                <p class="u-field-label">Avatar accent color</p>
                <p style="font-size:11px;color:var(--muted);margin:-4px 0 10px;">Applied when photo is unassigned.</p>
                <div class="u-swatches" style="margin-bottom:16px;">
                    @foreach ($avatarChoices as $key => $hues)
                        <label class="u-swatch"
                               style="background:linear-gradient(135deg,{{ $hues[0] }},{{ $hues[1] }});"
                               title="{{ ucfirst($key) }}"
                               data-hue-a="{{ $hues[0] }}"
                               data-hue-b="{{ $hues[1] }}">
                            <input type="radio" name="avatar_theme_picker" value="{{ $key }}"
                                   {{ $currentAvatar === $key ? 'checked' : '' }}
                                   onclick="document.getElementById('avatarThemeField').value=this.value; previewAvatarTheme(this.parentElement);">
                        </label>
                    @endforeach
                </div>

                <div class="ap-form-footer">
                    <span style="font-size:11px;color:var(--muted);">
                        @if ($user->updated_at)
                            Last profile update: {{ $user->updated_at->format('M j, Y \a\t g:i A') }}
                        @else
                            No updates yet
                        @endif
                    </span>
                    <div style="display:flex;gap:8px;">
                        <button type="reset" class="u-btn ghost" style="width:auto;padding:9px 16px;font-size:12px;" onclick="document.getElementById('avatarThemeField').value='{{ $currentAvatar }}'; var fb=document.getElementById('avatarPreviewFallback'); if(fb) fb.style.background='linear-gradient(135deg,{{ $avatarA }},{{ $avatarB }})';">Discard</button>
                        <button type="submit" class="u-btn" style="width:auto;padding:9px 18px;font-size:12px;">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Email & Authentication + Account status --}}
        <div>
            <div class="u-card" style="margin-top:0;">
                <h4 style="margin:0 0 2px;">Email &amp; Authentication</h4>
                <p style="margin:0;font-size:12px;color:var(--muted);">Primary login and ticket confirmation contact.</p>

                <p class="u-field-label" style="margin-top:16px;">Registered email</p>
                <div class="ap-static-field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--muted);flex-shrink:0;"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                    <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $user->email ?? 'guest@example.com' }}</span>
                    @if ($isVerified)
                        <span class="rb-badge rb-badge-teal">Verified</span>
                    @else
                        <span class="rb-badge" style="background:var(--amber-pale);color:var(--amber-deep);">Unverified</span>
                    @endif
                </div>
                <p style="font-size:10.5px;color:var(--muted);margin:6px 0 0;">Change of registered email is coming soon.</p>

                <p class="u-field-label" style="margin-top:16px;">Password</p>
                <div class="ap-static-field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--muted);flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span style="flex:1;letter-spacing:2px;color:var(--muted);">••••••••••••</span>
                    <span class="rb-badge rb-badge-neutral">Coming soon</span>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:16px;">
                    <div>
                        <div style="font-size:12.5px;font-weight:700;color:var(--ink);">Two-Factor Authentication</div>
                        <div style="font-size:10.5px;color:var(--muted);">
                            @if ($user->two_factor_enabled ?? false)
                                Enabled — a code is emailed to you at every login.
                            @else
                                Get a one-time code by email each time you log in.
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('user.account.two-factor.toggle') }}">
                        @csrf
                        <button type="submit" class="rb-badge {{ ($user->two_factor_enabled ?? false) ? 'rb-badge-teal' : 'rb-badge-neutral' }}" style="border:none;cursor:pointer;">
                            {{ ($user->two_factor_enabled ?? false) ? 'Enabled — Turn off' : 'Turn on' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="u-card" style="margin-top:14px;background:var(--teal-pale);border-color:var(--teal);">
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <div class="rb-confirm-icon" style="flex-shrink:0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <h4 style="margin:0 0 3px;">Account Status: {{ $isActive ? 'Good Standing' : ucfirst($user->status ?? 'Active') }}</h4>
                        <p style="margin:0;font-size:12px;color:var(--ink-soft);line-height:1.6;">Your account is {{ $isActive ? 'active and verified' : ($user->status ?? 'active') }}. Digital waivers, e-signatures, and booking history are safely linked to this profile.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="only-mobile">
        @csrf
        <button type="submit" class="u-btn ghost">Logout</button>
    </form>

@endsection

@push('scripts')
<script>
    // Instant preview when a color swatch is picked — only matters
    // when no profile photo is set, since a photo always takes visual
    // priority over the accent-color fallback.
    function previewAvatarTheme(swatchLabel) {
        var fallback = document.getElementById('avatarPreviewFallback');
        if (!fallback) return; // a photo is already showing — color swatch won't be visible anyway
        var a = swatchLabel.dataset.hueA;
        var b = swatchLabel.dataset.hueB;
        fallback.style.background = 'linear-gradient(135deg,' + a + ',' + b + ')';
    }

    // Instant client-side preview before the form submits
    document.getElementById('avatarPhotoInput').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;

        document.getElementById('avatarPhotoLabel').textContent = 'Uploading...';

        var reader = new FileReader();
        reader.onload = function (evt) {
            var existingImg = document.getElementById('avatarPreviewImg');
            var fallback = document.getElementById('avatarPreviewFallback');

            if (existingImg) {
                existingImg.src = evt.target.result;
            } else if (fallback) {
                var img = document.createElement('img');
                img.src = evt.target.result;
                img.id = 'avatarPreviewImg';
                img.alt = 'Profile photo';
                img.className = 'ap-hero-avatar';
                img.style.objectFit = 'cover';
                fallback.replaceWith(img);
            }
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush
