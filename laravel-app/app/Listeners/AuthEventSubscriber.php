<?php

namespace App\Listeners;

use App\Services\SystemLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Events\Dispatcher;

class AuthEventSubscriber
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        $ip = request()->ip();

        // I-check bago mag-log para hindi madamay ang kasalukuyang login sa history
        $newIp = SystemLogger::isNewIpForUser($user->getAuthIdentifier(), $ip);
        $offHours = SystemLogger::isOffHours();

        SystemLogger::record(
            'login_success',
            "Matagumpay na login ni {$user->email}.",
            'info',
            [],
            false,
            $user->getAuthIdentifier(),
            $user->email
        );

        if ($newIp) {
            SystemLogger::anomaly(
                'login_new_ip',
                "Login ni {$user->email} mula sa bagong IP ({$ip}).",
                'warning',
                ['ip' => $ip],
                $user->getAuthIdentifier(),
                $user->email
            );
        }

        if ($offHours) {
            SystemLogger::anomaly(
                'login_off_hours',
                "Login ni {$user->email} sa labas ng normal na oras (" . now()->format('h:i A') . ').',
                'warning',
                [],
                $user->getAuthIdentifier(),
                $user->email
            );
        }
    }

    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? null;

        SystemLogger::record(
            'login_failed',
            'Bigong login attempt' . ($email ? " para sa {$email}." : '.'),
            'warning',
            [],
            false,
            $event->user?->getAuthIdentifier(),
            $email
        );

        SystemLogger::checkBruteForce(request()->ip(), $email);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            SystemLogger::record(
                'logout',
                "Nag-logout si {$event->user->email}.",
                'info',
                [],
                false,
                $event->user->getAuthIdentifier(),
                $event->user->email
            );
        }
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        SystemLogger::anomaly(
            'password_reset',
            "Na-reset ang password ni {$event->user->email}.",
            'warning',
            [],
            $event->user->getAuthIdentifier(),
            $event->user->email
        );
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Failed::class => 'handleFailed',
            Logout::class => 'handleLogout',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }
}
