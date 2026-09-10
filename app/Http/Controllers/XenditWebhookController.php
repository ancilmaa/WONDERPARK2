<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives payment status notifications from Xendit (Payment Request API).
 *
 * Route MUST be excluded from CSRF verification (see bootstrap/app.php) —
 * Xendit posts here directly, it doesn't have a Laravel session/CSRF token.
 *
 * Docs: https://docs.xendit.co/apidocs/payment-webhook-notification
 */
class XenditWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $token = $request->header('x-callback-token');
        $expected = config('services.xendit.webhook_token');

        if (empty($expected) || $token !== $expected) {
            Log::warning('Xendit webhook: bad or missing x-callback-token');

            return response()->json(['message' => 'invalid token'], 401);
        }

        $payload = $request->json()->all();
        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];

        // "payment.capture" is the event Xendit sends for Payment Request
        // status updates (SUCCEEDED / FAILED). We only act on SUCCEEDED —
        // FAILED/other statuses just leave the booking as-is so the
        // customer can retry from the review page.
        if ($event !== 'payment.capture' || ($data['status'] ?? null) !== 'SUCCEEDED') {
            return response()->json(['message' => 'ignored']);
        }

        $referenceId = $data['reference_id'] ?? null;

        if (empty($referenceId)) {
            return response()->json(['message' => 'no reference_id'], 200);
        }

        $booking = Booking::where('xendit_reference_id', $referenceId)->first();

        if (!$booking) {
            Log::warning('Xendit webhook: no booking found for reference_id', ['reference_id' => $referenceId]);

            return response()->json(['message' => 'booking not found'], 200);
        }

        // Idempotent — Xendit retries webhooks, so don't re-confirm/log an
        // already-confirmed booking.
        if ($booking->status !== 'confirmed') {
            $booking->update([
                'status'               => 'confirmed',
                'payment_method'       => 'gcash',
                'payment_verified_at'  => now(),
            ]);
        }

        return response()->json(['message' => 'ok']);
    }
}
