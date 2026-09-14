<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class AccountController extends Controller
{
    public function index()
    {
        $users = DB::table('users')
            ->where('role', '!=', 'customer')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('accounts.create', compact('users'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'password' => 'required|min:6',
            'role'     => 'required|in:cashier,tl,manager,admin',
        ]);
        DB::table('users')->insert([
            'fullname'   => $request->fullname,
            'username'   => $request->username,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'status'     => 'active',
            'created_at' => now(),
        ]);
        return redirect('/accounts')->with('success', 'Account created successfully!');
    }
    public function update(Request $request, int $user_id)
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user_id . ',user_id|max:255',
            'role'     => 'required|in:cashier,tl,manager,admin',
        ]);
        $data = [
            'fullname' => $request->fullname,
            'username' => $request->username,
            'role'     => $request->role,
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        DB::table('users')->where('user_id', $user_id)->update($data);
        return redirect('/accounts')->with('success', 'Account updated successfully!');
    }
    public function destroy(int $user_id)
    {
        DB::table('users')->where('user_id', $user_id)->delete();
        return redirect('/accounts')->with('success', 'Account deleted successfully!');
    }
    public function create()
    {
        $users = DB::table('users')
            ->where('role', '!=', 'customer')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('accounts.create', compact('users'));
    }
}