<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Standalone notifications dashboard.
     * Supports ?status=all|unread|read and ?type=all|booking|inventory|pos
     *
     * NOTE: the type values here were updated to match what the app
     * actually creates now — BookingObserver writes 'booking',
     * InventoryController writes 'inventory' (covers add/edit/delete/
     * restock/low-stock/out-of-stock), and PosController writes 'pos'.
     * The old 'reservation' / 'low_stock' values are no longer produced
     * anywhere, which is why filtering by them silently matched nothing.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $type = $request->query('type', 'all');

        $query = Notification::latestFirst();

        if ($status === 'unread') {
            $query->where('is_read', false);
        } elseif ($status === 'read') {
            $query->where('is_read', true);
        }

        if (in_array($type, ['booking', 'inventory', 'pos'], true)) {
            $query->where('type', $type);
        }

        $notifications = $query->paginate(15)->withQueryString();

        $stats = [
            'total'     => Notification::count(),
            'unread'    => Notification::unread()->count(),
            'booking'   => Notification::where('type', 'booking')->count(),
            'inventory' => Notification::where('type', 'inventory')->count(),
            'pos'       => Notification::where('type', 'pos')->count(),
        ];

        return view('notifications.index', compact('notifications', 'stats', 'status', 'type'));
    }

    /**
     * Mark a single notification as read. Called via fetch() when the
     * user opens the notification's detail popup (view = read), and
     * also reachable as a plain form/redirect fallback.
     */
    public function markRead(Notification $notification): JsonResponse|RedirectResponse
    {
        $notification->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * Mark-all-read. Used by both the sidebar's fetch() call (JSON)
     * and a plain form submit from the dashboard (redirect back).
     */
    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        Notification::unread()->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * Delete a single notification. Used by the trash-icon button on
     * the full Notifications page.
     */
    public function destroy(Notification $notification): JsonResponse|RedirectResponse
    {
        $notification->delete();

        if (request()->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * Bulk mark-as-read for selected notifications from the checkboxes
     * on the full Notifications page.
     */
    public function bulkMarkRead(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:notifications,id'],
        ]);

        $updated = Notification::whereIn('id', $validated['ids'])->update(['is_read' => true]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'updated' => $updated]);
        }

        return back()->with('status', $updated . ' notification(s) marked as read.');
    }

    /**
     * Bulk-delete selected notifications from the checkboxes on the
     * full Notifications page ("Select all" + "Delete selected" bar).
     */
    public function bulkDestroy(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:notifications,id'],
        ]);

        $deleted = Notification::whereIn('id', $validated['ids'])->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'deleted' => $deleted]);
        }

        return back()->with('status', $deleted . ' notification(s) deleted.');
    }

public function poll(Request $request): JsonResponse
{
    $notifications = Notification::latestFirst()
        ->limit(6)
        ->get()
        ->map(function (Notification $n) {
            return [
                'id'       => $n->id,
                'title'    => $n->title,
                'message'  => $n->message,
                'type'     => $n->type,
                'url'      => $n->url,
                'is_read'  => (bool) $n->is_read,
                'time'     => $n->created_at->diffForHumans(),
                'read_url' => route('notifications.read', $n->id),
            ];
        });

    return response()->json([
        'unread_count' => Notification::unread()->count(),
        'total_count'  => Notification::count(),
        'notifications' => $notifications,
    ]);
}
}