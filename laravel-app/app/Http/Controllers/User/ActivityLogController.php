<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * GET /app/activity — the signed-in user's own activity log.
     */
    public function index(Request $request)
    {
        $userId = (int) session('user_id');

        $group = (string) $request->query('type', '');
        $allowed = ['auth', 'booking', 'waiver', 'account', 'security'];

        $query = DB::table('system_logs')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if (in_array($group, $allowed, true)) {
            $query->where('action', 'like', $group . '.%');
        } else {
            $group = '';
        }

        $logs = $query->paginate(15)->withQueryString();
        $user = User::find($userId);

        return view('user.activity', compact('logs', 'group', 'user'));
    }
}
