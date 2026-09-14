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
}