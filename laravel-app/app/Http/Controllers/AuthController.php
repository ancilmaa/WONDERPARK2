<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (session()->has('user_id')) {
            return redirect('/home');
        }
        return view('auth.login');
    }
   public function login(Request $request)
{
    $username = trim($request->input('username', ''));
    $password = $request->input('password', '');

    if ($username === '' || $password === '') {
        return back()->with('error', 'Please enter username and password.');
    }

    $user = DB::table('users')
              ->where('username', $username)
              ->first();

    if ($user && $user->status === 'active' && password_verify($password, $user->password)) {

    if (!empty($user->two_factor_enabled)) {
        $code = (string) random_int(100000, 999999);

        DB::table('users')->where('user_id', $user->user_id)->update([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your WonderPark verification code is: {$code}\n\nThis code expires in 10 minutes. If you didn't try to log in, you can ignore this email.",
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Your WonderPark verification code');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('2FA email failed: ' . $e->getMessage());
        }

        session([
            'twofa_pending_user_id' => $user->user_id,
            'twofa_redirect' => $user->role === 'customer' ? '/app/waiver' : ($user->role === 'cashier' ? '/home' : '/ml-forecast'),
        ]);

        return redirect('/two-factor');
    }

    session()->regenerate();
    session([
        'user_id' => $user->user_id,
        'fullname' => $user->fullname,
        'email' => $user->email,
        'role' => $user->role,
    ]);

      if ($user->role === 'customer') {
        return redirect('/app/waiver');
    }

    if ($user->role === 'cashier') {
        return redirect('/home');
    }

    return redirect('/ml-forecast');
}
    return back()->with('error', 'Invalid username or password.');
}

    public function showTwoFactorForm()
    {
        if (!session()->has('twofa_pending_user_id')) {
            return redirect('/login');
        }
        return view('auth.two-factor');
    }

    public function verifyTwoFactor(Request $request)
    {
        $pendingId = session('twofa_pending_user_id');
        if (!$pendingId) {
            return redirect('/login');
        }

        $code = trim($request->input('code', ''));
        $user = DB::table('users')->where('user_id', $pendingId)->first();

        if (!$user) {
            session()->forget(['twofa_pending_user_id', 'twofa_redirect']);
            return redirect('/login')->with('error', 'Session expired. Please log in again.');
        }

        if (empty($user->two_factor_code) || $user->two_factor_code !== $code) {
            return back()->with('error', 'Incorrect code. Please try again.');
        }

        if (!$user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        DB::table('users')->where('user_id', $user->user_id)->update([
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ]);

        $redirect = session('twofa_redirect', '/app/waiver');
        session()->forget(['twofa_pending_user_id', 'twofa_redirect']);

        session()->regenerate();
        session([
            'user_id' => $user->user_id,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect($redirect);
    }

    public function resendTwoFactor()
    {
        $pendingId = session('twofa_pending_user_id');
        if (!$pendingId) {
            return redirect('/login');
        }

        $user = DB::table('users')->where('user_id', $pendingId)->first();
        if (!$user) {
            return redirect('/login');
        }

        $code = (string) random_int(100000, 999999);
        DB::table('users')->where('user_id', $user->user_id)->update([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your WonderPark verification code is: {$code}\n\nThis code expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Your WonderPark verification code');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('2FA resend email failed: ' . $e->getMessage());
        }

        return back()->with('status', 'A new code has been sent to your email.');
    }
    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }

    public function showRegisterForm()
    {
        if (session()->has('user_id')) {
            return redirect('/home');
        }
        return view('auth.register');
    }

    public function register(Request $request)
{
    $fullname = trim($request->input('fullname', ''));
    $username = trim($request->input('username', ''));
    $email    = trim($request->input('email', ''));
    $age      = $request->input('age', '');
    $password = $request->input('password', '');
    $confirm  = $request->input('password_confirmation', '');

    if ($fullname === '' || $username === '' || $email === '' || $age === '' || $password === '') {
        return back()->withInput()->with('error', 'Please fill in all fields.');
    }

    if (!is_numeric($age) || $age < 1 || $age > 120) {
        return back()->withInput()->with('error', 'Please enter a valid age.');
    }

    if ($password !== $confirm) {
        return back()->withInput()->with('error', 'Passwords do not match.');
    }

    if (strlen($password) < 8) {
        return back()->withInput()->with('error', 'Password must be at least 8 characters.');
    }

    if (DB::table('users')->where('username', $username)->exists()) {
        return back()->withInput()->with('error', 'Username is already taken.');
    }

    if (DB::table('users')->where('email', $email)->exists()) {
        return back()->withInput()->with('error', 'Email is already registered.');
    }

    $userId = DB::table('users')->insertGetId([
        'name'       => $fullname,
        'username'   => $username,
        'fullname'   => $fullname,
        'email'      => $email,
        'age'        => (int) $age,
        'status'     => 'active',
        'role'       => 'customer',
        'password'   => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => now(),
        'updated_at' => now(),
    ], 'user_id');

    session()->regenerate();

    session([
    'user_id'  => $userId,
    'fullname' => $fullname,
    'email'    => $email,
    'role'     => 'customer',
]);

return redirect('/app/waiver');
}

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = DB::table('users')->where('email', $request->email)->first();

        // Hindi natin sasabihin kung existing ba yung email o hindi (para sa security) —
        // palaging same generic message, pero padadalhan lang talaga kung meron.
        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('users')->where('user_id', $user->user_id)->update([
                'password_change_code'       => $code,
                'password_change_expires_at' => now()->addMinutes(10),
            ]);

            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Your WonderPark password reset code is: {$code}\n\nThis code expires in 10 minutes. If you didn't request this, you can safely ignore this email.",
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Reset your WonderPark password');
                    }
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Password reset email failed: ' . $e->getMessage());
            }

            session(['reset_pending_user_id' => $user->user_id]);
        }

        return redirect('/reset-password')
            ->with('status', 'If that email is registered, a reset code has been sent to it.');
    }

    public function showResetForm()
    {
        if (!session()->has('reset_pending_user_id')) {
            return redirect('/forgot-password');
        }
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $pendingId = session('reset_pending_user_id');
        if (!$pendingId) {
            return redirect('/forgot-password');
        }

        $request->validate([
            'code' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = DB::table('users')->where('user_id', $pendingId)->first();

        if (!$user) {
            session()->forget('reset_pending_user_id');
            return redirect('/forgot-password')->with('error', 'Something went wrong. Please try again.');
        }

        if (empty($user->password_change_code) || $user->password_change_code !== $request->code) {
            return back()->with('error', 'Incorrect code. Please try again.');
        }

        if (!$user->password_change_expires_at || now()->greaterThan($user->password_change_expires_at)) {
            return back()->with('error', 'This code has expired. Please request a new one.');
        }

        DB::table('users')->where('user_id', $user->user_id)->update([
            'password'                   => password_hash($request->new_password, PASSWORD_DEFAULT),
            'password_change_code'       => null,
            'password_change_expires_at' => null,
        ]);

        session()->forget('reset_pending_user_id');

        return redirect('/login')->with('status', 'Your password has been reset. You can now log in.');
    }

    public function resendResetCode()
    {
        $pendingId = session('reset_pending_user_id');
        if (!$pendingId) {
            return redirect('/forgot-password');
        }

        $user = DB::table('users')->where('user_id', $pendingId)->first();
        if (!$user) {
            return redirect('/forgot-password');
        }

        $code = (string) random_int(100000, 999999);
        DB::table('users')->where('user_id', $user->user_id)->update([
            'password_change_code'       => $code,
            'password_change_expires_at' => now()->addMinutes(10),
        ]);

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your WonderPark password reset code is: {$code}\n\nThis code expires in 10 minutes.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Reset your WonderPark password');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Password reset resend email failed: ' . $e->getMessage());
        }

        return back()->with('status', 'A new code has been sent to your email.');
    }
}