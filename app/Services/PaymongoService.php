<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymongoService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = (string) config('services.paymongo.secret_key');
    }

    /**
     * Create a PayMongo Hosted Checkout Session for a booking's GCash
     * payment. Returns ['id' => 'cs_...', 'checkout_url' => 'https://...'].
     *
     * The customer is redirected to checkout_url — PayMongo's own hosted
     * page (the "Open in GCash / scan this QR" screen) — and pays there.
     * We never touch card/GCash credentials ourselves.
     */
    public function createCheckoutSession(Booking $booking): array
    {
        if (empty($this->secretKey)) {
            throw new RuntimeException('PAYMONGO_SECRET_KEY is not set in .env.');
        }

        $packageLabel = ucfirst(str_replace('_', ' ', $booking->package));

        $response = Http::withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->post('https://api.paymongo.com/v2/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'line_items' => [[
                            'name'     => "REKS Wonder Park — {$packageLabel}",
                            'amount'   => (int) round(((float) $booking->price) * 100),
                            'currency' => 'PHP',
                            'quantity' => 1,
                        ]],
                        'payment_method_types' => ['gcash'],
                        'success_url' => route('user.bookings.paymongo.success', $booking->id),
                        'cancel_url'  => route('user.bookings.paymongo.cancel', $booking->id),
                        'reference_number' => 'BOOKING-' . $booking->id,
                        // This is how the webhook later finds the booking again.
                        'metadata' => [
                            'booking_id' => (string) $booking->id,
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('PayMongo checkout session creation failed', [
                'booking_id' => $booking->id,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);

            throw new RuntimeException('Could not start GCash payment. Please try again.');
        }

        $data = $response->json('data');

        return [
            'id'           => $data['id'],
            'checkout_url' => $data['attributes']['checkout_url'],
        ];
    }

    /**
     * Verify that a webhook request genuinely came from PayMongo, per
     * https://docs.paymongo.com/docs/developer-tools-webhook-setup-management
     *
     * @param string $rawPayload The raw (unparsed) request body.
     * @param string $signatureHeader The raw "Paymongo-Signature" header value.
     */
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool
    {
        $webhookSecret = (string) config('services.paymongo.webhook_secret');

        if (empty($webhookSecret) || empty($signatureHeader)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $chunk) {
            [$key, $value] = array_pad(explode('=', $chunk, 2), 2, null);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? null;
        // 'te' = test-mode signature, 'li' = live-mode signature — only
        // one of the two is ever non-empty, matching the key's mode.
        $providedSignature = !empty($parts['te']) ? $parts['te'] : ($parts['li'] ?? null);

        if (!$timestamp || !$providedSignature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $rawPayload, $webhookSecret);

        return hash_equals($expectedSignature, $providedSignature);
    }
}
