<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('partials.bottomnav', function ($view) {
            $view->with('u', session()->has('user_id') ? (object) [
                'name' => session('fullname'),
                'email' => session('email'),
            ] : null);
        });
    }
}