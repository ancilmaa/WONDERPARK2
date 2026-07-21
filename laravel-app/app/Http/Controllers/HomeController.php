<?php
namespace App\Http\Controllers;
class HomeController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        if (session('role') === 'customer') {
            return redirect()->route('user.dashboard');
        }

        return view('index');
    }
}
