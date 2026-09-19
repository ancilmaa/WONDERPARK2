<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Show the customer's Account page.
     *
     * GET /app/dashboard
     */
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        // Same definition of "upcoming" used on the My Bookings stats
        // (BookingController@index): status = confirmed.
        // NOTE: User's primary key is "user_id", not "id" — using
        // $user->id here was a bug (wrong column) introduced earlier.
        $upcomingCount = Booking::where('user_id', $user->user_id)
            ->where('status', 'confirmed')
            ->count();

        return view('user.dashboard', compact('user', 'upcomingCount'));
    }

    /**
     * Update the customer's name, avatar color theme, and/or avatar photo.
     *
     * POST /app/account
     */
    public function update(\Illuminate\Http\Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect('/login')->with('error', 'Your session has expired. Please log in again.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:60',
            'avatar_theme' => 'nullable|string|max:60',
            'avatar_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user->name = $validated['name'];

        if (!empty($validated['avatar_theme'])) {
            $user->avatar_theme = $validated['avatar_theme'];
        }

        if ($request->hasFile('avatar_photo')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('avatar_photo')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $saved = $user->save();
        $user->refresh();

        // keep the session's cached fullname in sync with the name field
        session(['fullname' => $user->name]);

        if (!$saved) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Could not save your changes. Please try again.');
        }

        // Echoes back what's actually in the DB right now, so the banner
        // itself proves whether the save really went through.
        return redirect()->route('user.dashboard')
            ->with('success', 'Profile updated: name is now "' . $user->name . '", avatar color is "' . $user->avatar_theme . '".');
    }

    /**
     * Toggle email-based two-factor authentication on/off for the account.
     *
     * POST /app/account/two-factor
     */
    public function toggleTwoFactor(\Illuminate\Http\Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect('/login')->with('error', 'Your session has expired. Please log in again.');
        }

        $user->two_factor_enabled = !$user->two_factor_enabled;

        // Clear any stale pending code when toggling.
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        $status = $user->two_factor_enabled ? 'enabled' : 'disabled';

        return redirect()->route('user.dashboard')
            ->with('success', "Two-Factor Authentication has been {$status}.");
    }
}
