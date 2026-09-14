<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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

        return view('user.dashboard', compact('user'));
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

        $user->save();

        // keep the session's cached fullname in sync with the name field
        session(['fullname' => $user->name]);

        return back()->with('success', 'Profile updated successfully.');
    }
}
