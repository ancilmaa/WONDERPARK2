<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | WonderPark Amusement Com Inc.</title>
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
                    <h2>Three worlds of thrill, one login away.</h2>
                    <p>Manage bookings, inventory, and the front desk for Roller Fever, Dino Adventure, and the Field of
                        Rides — all in one place.</p>
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
                <span class="ticket-label coral">You may Access</span>
                <h1>Welcome back</h1>
                <p class="auth-sub">Sign in to continue to your dashboard.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ url('/login') }}">
                    @csrf

                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required
                            autofocus autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn btn-primary">Log In</button>
                </form>

                <div class="auth-divider">or continue with</div>

                <div class="social-row">
                    <a href="{{ url('/auth/google/redirect') }}" class="btn-social google">
                        <svg viewBox="0 0 24 24">
                            <path fill="#4285F4"
                                d="M23.52 12.27c0-.85-.08-1.66-.22-2.45H12v4.63h6.47c-.28 1.48-1.13 2.74-2.4 3.58v2.98h3.88c2.27-2.09 3.57-5.17 3.57-8.74z" />
                            <path fill="#34A853"
                                d="M12 24c3.24 0 5.96-1.07 7.95-2.9l-3.88-2.98c-1.08.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.27v3.07C3.25 21.3 7.31 24 12 24z" />
                            <path fill="#FBBC05"
                                d="M5.27 14.31A7.2 7.2 0 0 1 4.9 12c0-.8.14-1.58.37-2.31V6.62H1.27A11.97 11.97 0 0 0 0 12c0 1.93.46 3.76 1.27 5.38l4-3.07z" />
                            <path fill="#EA4335"
                                d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.31 0 3.25 2.7 1.27 6.62l4 3.07C6.22 6.86 8.87 4.75 12 4.75z" />
                        </svg>
                        Continue with Google
                    </a>
                    <a href="{{ url('/auth/facebook/redirect') }}" class="btn-social facebook">
                        <svg viewBox="0 0 24 24">
                            <path fill="#1877F2"
                                d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z" />
                        </svg>
                        Continue with Facebook
                    </a>
                </div>

                <p class="auth-signup">Don't have an account? <a href="{{ url('/register') }}">Create one</a></p>
            </div>
        </div>
        <script>
            document.querySelectorAll('.bg-slide[data-bg]').forEach(function(el) {
                el.style.backgroundImage = "url('" + el.dataset.bg + "')";
            });
        </script>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.bg-slide').forEach(el => {
                el.style.backgroundImage = `url('${el.dataset.bg}')`;
            });
        });
    </script>
</body>

</html>
