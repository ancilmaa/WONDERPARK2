{{--
    resources/views/user/dashboard.blade.php
    Route: GET  /user/account  ->  user.dashboard
           POST /user/account  ->  user.account.update
--}}
@extends('layouts.user')

@section('title', 'Account')
@section('page-title', 'Account')
@section('page-subtitle', 'Manage your REKS account')

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
    @endphp

    <div class="u-grid-2">
    <div class="u-profile-card">
        <div class="u-profile-top">
            @if ($hasPhoto)
                <img src="{{ asset('storage/' . $user->avatar_path) }}"
                     alt="Profile photo"
                     class="u-avatar-lg"
                     style="object-fit:cover;">
            @else
                <div class="u-avatar-lg" style="--u-avatar-a:{{ $avatarA }};--u-avatar-b:{{ $avatarB }};">
                    {{ strtoupper(substr($user->name ?? 'G', 0, 1)) }}
                </div>
            @endif
            <div>
                <h4>Hello, {{ $user->name ?? 'Guest' }} 👋</h4>
                <span class="u-role-pill">Customer Account</span>
            </div>
        </div>

        <p class="u-field-label">Display name</p>

        <form method="POST" action="{{ route('user.account.update') }}" enctype="multipart/form-data" class="u-name-row" id="accountForm">
            @csrf
            <input type="hidden" name="avatar_theme" value="{{ $currentAvatar }}" id="avatarThemeField">
            <input type="text" name="name" class="u-input" value="{{ old('name', $user->name ?? '') }}" maxlength="60" required>
            <button type="submit" class="u-icon-btn save" title="Save changes">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
            </button>
        </form>

        <p class="u-field-label">Profile photo</p>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <label for="avatarPhotoInput" class="u-btn ghost" style="width:auto;display:inline-block;padding:9px 16px;font-size:12px;cursor:pointer;margin:0;">
                Change photo
            </label>
            <input type="file" id="avatarPhotoInput" name="avatar_photo" accept="image/png,image/jpeg,image/webp"
                   form="accountForm" style="display:none;" onchange="document.getElementById('accountForm').submit();">
            <span style="font-size:11.5px;color:var(--muted);">JPG, PNG, or WEBP. Max 2MB.</span>
        </div>

        <p class="u-field-label">Avatar color</p>
        <p style="font-size:11.5px;color:var(--muted);margin:-4px 0 10px;">Used when you don't have a profile photo set.</p>
        <div class="u-swatches">
            @foreach ($avatarChoices as $key => $hues)
                <label class="u-swatch"
                       style="background:linear-gradient(135deg,{{ $hues[0] }},{{ $hues[1] }});"
                       title="{{ ucfirst($key) }}">
                    <input type="radio" name="avatar_theme_picker" value="{{ $key }}"
                           {{ $currentAvatar === $key ? 'checked' : '' }}
                           onclick="document.getElementById('avatarThemeField').value=this.value;document.getElementById('accountForm').submit();">
                </label>
            @endforeach
        </div>
    </div>

    <div>
        <div class="u-card">
            <h4>Email</h4>
            <p>{{ $user->email ?? 'guest@example.com' }}</p>
        </div>

        <div class="u-card" style="border-left-color:var(--teal);background:var(--teal-pale);">
            <h4>Account status</h4>
            <p>Your account is active and in good standing. Signature and booking history are tied to this profile.</p>
        </div>
    </div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="only-mobile">
        @csrf
        <button type="submit" class="u-btn ghost">Logout</button>
    </form>

@endsection