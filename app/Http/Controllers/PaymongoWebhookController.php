<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaymongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymongoWebhookController extends Controller
{
    public function __construct(private PaymongoService $paymongo)
    {
    }

    /**
     * Receive payment confirmation events from PayMongo. This is the ONLY
     * place a GCash-via-PayMongo booking gets marked 'confirmed' — never
     * trust the browser redirect back from PayMongo for that, since a
     * customer could close the tab or spoof that request.
     *
     * POST /webhooks/paymongo  (CSRF-exempt — see bootstrap/app.php)
     */
    public function handle(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signatureHeader = $request->header('Paymongo-Signature', '');

        if (!$this->paymongo->verifyWebhookSignature($rawPayload, $signatureHeader)) {
            Log::warning('PayMongo webhook: signature verification failed.');

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($rawPayload, true);
        $eventType = $event['data']['attributes']['type'] ?? null;

        if ($eventType !== 'checkout_session.payment.paid') {
            // Acknowledge other event types so PayMongo doesn't keep retrying.
            return response()->json(['received' => true]);
        }

        $session = $event['data']['attributes']['data'] ?? null;
        $bookingId = $session['attributes']['metadata']['booking_id'] ?? null;
        $paymentId = $session['attributes']['payments'][0]['id'] ?? null;

        if (!$bookingId) {
            Log::warning('PayMongo webhook: no booking_id in checkout session metadata.', ['session' => $session]);

            return response()->json(['received' => true]);
        }

        $booking = Booking::find($bookingId);

        if (!$booking) {
            Log::warning('PayMongo webhook: booking not found.', ['booking_id' => $bookingId]);

            return response()->json(['received' => true]);
        }

        // Idempotent on purpose — PayMongo may send the same event more than once.
        if ($booking->status !== 'confirmed') {
            $booking->update([
                'payment_method'       => 'gcash',
                'status'               => 'confirmed',
                'paymongo_payment_id'  => $paymentId,
                'payment_confirmed_at' => now(),
            ]);
        }

        return response()->json(['received' => true]);
    }
}
