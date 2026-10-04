<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | WonderPark Amusement Com Inc.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body>

    <div class="auth-split">

        <div class="auth-visual">
            <div class="bg-slide" data-bg="{{ asset('images/vikings.jpg') }}"></div>
            <div class="bg-slide" data-bg="{{ asset('images/roller-fever.jpg') }}"></div>
            <div class="auth-visual-content">
                <a href="{{ route('home') }}" class="logo">WonderPark<span class="dot">•</span>Amusement</a>
                <div class="auth-visual-copy">
                    <span class="ticket-label on-dark">Lipa Branch</span>
                    <h2>One account, every ride and every visit.</h2>
                    <p>Book, manage, and enjoy Roller Fever, Dino Adventure, and the Field of Rides — all in one place.
                    </p>
                    <div class="auth-visual-stamps">
                        <span class="stamp">Roller Fever</span>
                        <span class="stamp">Dino Adventure</span>
                        <span class="stamp">Field of Rides</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-side" style="position:relative;">
            <a href="{{ route('home') }}" class="auth-back-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <div class="auth-card">
                <span class="ticket-label coral">New Guest</span>
                <h1>Create your account</h1>
                <p class="auth-sub">Sign up to start booking your visit.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
                @if ($errors->has('terms'))
                    <div class="auth-error">{{ $errors->first('terms') }}</div>
                @endif

                <form method="POST" action="{{ url('/register') }}">
                    @csrf

                    <div class="field">
                        <label for="fullname">Full Name</label>
                        <input id="fullname" type="text" name="fullname" value="{{ old('fullname') }}" required
                            autofocus autocomplete="name">
                    </div>

                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required
                            autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email">
                    </div>

                    <div class="field">
                        <label for="age">Age</label>
                        <input type="number" name="age" id="age" min="1" max="120"
                            value="{{ old('age') }}" placeholder="e.g. 25" required>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password">
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            autocomplete="new-password">
                    </div>

                    <label class="auth-check" for="terms">
                        <input type="checkbox" id="terms" name="terms" value="1" required
                            {{ old('terms') ? 'checked' : '' }}>
                        <span>
                            I agree to the
                            <a href="#" data-open="termsModal">Terms &amp; Conditions</a> and
                            <a href="#" data-open="privacyModal">Privacy Notice</a>.
                        </span>
                    </label>

                    <button type="submit" class="btn btn-primary">Create Account</button>
                </form>

                <p class="auth-signup">Already have an account? <a href="{{ url('/login') }}">Sign in</a></p>
            </div>
        </div>

    </div>

           @include('partials.legal-modals')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.bg-slide').forEach(function (el) {
                el.style.backgroundImage = "url('" + el.dataset.bg + "')";
            });
        });
    </script>
</body>

</html>