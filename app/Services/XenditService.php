<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Talks to Xendit's Payment Request API (v3/payment_requests) to create a
 * one-time GCash e-wallet payment and get back a redirect URL the customer
 * is sent to (Xendit's own hosted GCash page — same idea as the previous
 * PayMongo checkout session, just a different provider).
 *
 * Docs: https://docs.xendit.co/docs/gcash
 *       https://docs.xendit.co/apidocs/payment-webhook-notification
 */
class XenditService
{
    private const API_BASE = 'https://api.xendit.co';

    /**
     * Create a GCash payment request for a booking and return the details
     * the controller needs: where to redirect the customer, and the ids to
     * store on the booking so the webhook can match the payment back to it.
     *
     * @return array{redirect_url: string, payment_request_id: string, reference_id: string}
     */
    public function createGcashPayment(Booking $booking, string $successUrl, string $failureUrl): array
    {
        $secretKey = config('services.xendit.secret_key');

        if (empty($secretKey)) {
            throw new RuntimeException('XENDIT_SECRET_KEY is not set in .env');
        }

        // Unique per attempt (not just per booking) so re-tapping "Pay via
        // GCash Now" after a failed/abandoned attempt doesn't collide with
        // the previous reference_id.
        $referenceId = 'booking-' . $booking->id . '-' . Str::lower(Str::random(8));

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->post(self::API_BASE . '/v3/payment_requests', [
                'reference_id'    => $referenceId,
                'type'            => 'PAY',
                'country'         => 'PH',
                'currency'        => 'PHP',
                'request_amount'  => (float) $booking->price,
                'capture_method'  => 'AUTOMATIC',
                'channel_code'    => 'GCASH',
                'channel_properties' => [
                    'success_return_url' => $successUrl,
                    'failure_return_url' => $failureUrl,
                ],
                'description' => 'REKS booking #' . $booking->id,
                'metadata' => [
                    'booking_id' => $booking->id,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Xendit payment request creation failed', [
                'booking_id' => $booking->id,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);

            throw new RuntimeException('Could not start the GCash payment. Please try again.');
        }

        $data = $response->json();

        $redirectUrl = collect($data['actions'] ?? [])
            ->firstWhere('type', 'REDIRECT_CUSTOMER')['value'] ?? null;

        if (empty($redirectUrl)) {
            Log::error('Xendit payment request had no redirect action', [
                'booking_id' => $booking->id,
                'body'       => $data,
            ]);

            throw new RuntimeException('Could not start the GCash payment. Please try again.');
        }

        return [
            'redirect_url'        => $redirectUrl,
            'payment_request_id'  => $data['id'] ?? $data['payment_request_id'] ?? '',
            'reference_id'        => $referenceId,
        ];
    }
}
