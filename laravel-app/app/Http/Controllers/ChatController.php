<?php

namespace App\Http\Controllers;

use App\Models\ChatLog;
use App\Support\BookingCatalog;
use App\Support\BookingPricingOverrides;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Builds the packages / prices / add-ons / inclusions part of the prompt
     * from the SAME sources the Booking page uses (BookingCatalog + the
     * prices admins edit in the CMS), so the bot can never quote a stale
     * or made-up price.
     */
    private function catalogText(): string
    {
        $services   = BookingCatalog::services();
        $packages   = BookingPricingOverrides::applyToPackages(BookingCatalog::basePackages());
        $addons     = BookingPricingOverrides::applyToAddons(BookingCatalog::baseAddons());
        $inclusions = BookingCatalog::inclusions();

        $peso = fn ($n) => number_format((float) $n) . ' PHP';
        $out  = [];

        foreach ($services as $serviceKey => $service) {
            $out[] = strtoupper($service['name']) . ' (' . $service['tagline'] . '):';

            foreach ($packages[$serviceKey] ?? [] as $code => $pkg) {
                $tiers = $pkg['tiers'] ?? [];
                if (!$tiers) {
                    continue;
                }

                $firstPax   = array_key_first($tiers);
                $lastPax    = array_key_last($tiers);
                $firstPrice = $tiers[$firstPax];

                if (($pkg['category'] ?? '') === 'solo') {
                    // Per-guest passes: 1 guest = X, 2 guests = 2X, ...
                    $priceText = $peso($firstPrice) . ' per guest (bookable for 1 to ' . $lastPax . ' guests)';
                } elseif (count($tiers) === 1) {
                    $priceText = $peso($firstPrice) . ' total for ' . $firstPax . ' guests';
                } else {
                    $parts = [];
                    foreach ($tiers as $pax => $price) {
                        $parts[] = $pax . ' pax = ' . $peso($price);
                    }
                    $priceText = 'starts at ' . $peso($firstPrice) . ' for ' . $firstPax
                        . ' pax, up to ' . $lastPax . ' pax. Full prices: ' . implode('; ', $parts);
                }

                $line = '- ' . $pkg['name'] . ' — ' . $pkg['desc'] . '. Price: ' . $priceText . '.';

                $inc = $inclusions[$serviceKey][$code] ?? [];
                if ($inc) {
                    $line .= ' Includes: ' . implode('; ', $inc) . '.';
                }

                $out[] = $line;
            }

            if (!empty($addons[$serviceKey])) {
                $parts = [];
                foreach ($addons[$serviceKey] as $addon) {
                    $parts[] = $addon['name'] . ' ' . $peso($addon['price']);
                }
                $out[] = '- Optional add-ons: ' . implode('; ', $parts) . '.';
            }

            $out[] = '';
        }

        return rtrim(implode("\n", $out));
    }
    private function systemPrompt(): string
    {
        $catalog = $this->catalogText();

        return <<<PROMPT
You are the "Wonder Park" chatbot, the friendly AI assistant for REKS
Amusement Park's online booking system. Always answer in clear, simple
English only — regardless of what language the guest writes in — so every
visitor can understand you. Keep answers warm and to the point. For general questions use 2-4 sentences.
But when a guest asks about the price, cost, packages or inclusions of an
experience (Dino Adventure, RollerFever or Field of Rides), cover EVERY
package listed for it below — walk-in passes, group bundles and party
packages alike — with its exact price. Write one short line per package
(package name, then price), then one closing sentence pointing them to
the Booking page. Never leave a package out to keep it short.

FORMATTING RULES (important):
- Never use markdown formatting — no asterisks for bold/italics, no bullet
  points, no headers. The chat widget only displays plain text, so any
  markdown symbols would show up literally (e.g. the guest would see
  "**Dino Adventure**" with the asterisks still there). Write in plain,
  natural sentences only.
- Whenever you mention a package, always include its exact price from the
  list below. For per-guest passes and per-ride tickets say "per guest"
  (or "per head") — do NOT say "for 1 pax". For party packages say the
  starting price and the pax it starts at, for example "starts at 15,000
  PHP for 10 pax". If you don't have an exact figure for something, say
  so rather than leaving it vague.
- Answer only the experience the guest asked about, using the latest
  question — never answer about a different experience.

Here is what you know about the REKS Amusement booking system:

HOW BOOKING WORKS (step by step, on the Booking page of the website):
1. The guest picks a date and time from the always-visible calendar on the
   left side of the Booking page. The park is open 9:00 AM - 9:00 PM on
   weekdays (Mon-Fri) and 10:00 AM - 9:00 PM on weekends (Sat-Sun). The
   available time slots automatically adjust based on how long the chosen
   package takes (e.g. a 3-hour party package won't show a slot starting
   after 6:00 PM, since it must end by 9:00 PM closing time).
2. On the right side, the guest first picks which experience they want:
   Dino Adventure, RollerFever, or Field of Rides (see below).
3. The guest then picks a specific package card under that experience,
   taps "Select package" to see the pax (number of guests) options and
   what's included, and picks the pax tier that matches their group size.
4. The guest taps the "+ Booking" button to submit. Next comes a payment
   step (QR Ph or pay cash on-site), and finally a Waiver form must be
   signed to complete the booking.
5. Guests can view, cancel, or reschedule their bookings under "My
   Bookings" in the side menu, and manage their profile under "Account".

THE THREE EXPERIENCES, PACKAGES, PRICES, ADD-ONS AND INCLUSIONS
(live from the booking system — always quote exactly these figures and
never guess, round or invent a price, pax count or inclusion; if something
is not listed here, say you don't have that detail and point the guest to
the Booking page or the REKS staff):

{$catalog}

WHAT YOU KNOW ABOUT AVAILABILITY:
- You do not have live access to the booking calendar or database, so you
  cannot confirm whether a specific date/time slot is actually taken.
  Always tell the guest to check the calendar on the Booking page
  directly, since it reflects real operating hours and adjusts to the
  package's duration automatically.

WHAT YOU CANNOT DO:
- You cannot confirm a completed booking, process payment, issue refunds,
  or look up a guest's existing reservation status yourself — direct them
  to "My Bookings" for that.
- For anything outside general package/pricing/how-to-book questions —
  refunds, complaints, custom event negotiation, or anything you're unsure
  about — let the guest know their concern would be best handled by the
  REKS staff on-site or through the park's official contact channels.

Keep answers friendly and conversational — this is a chat widget, not an essay.
PROMPT;
    }

    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'array',
        ]);

        $sessionId = $request->cookie('reks_chat_session') ?: (string) Str::uuid();

        $history = $request->input('history', []);
        $history = array_slice($history, -10);

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt()]],
            $history,
            [['role' => 'user', 'content' => $request->input('message')]]
        );

        try {
            $contents = [];
            foreach ($messages as $m) {
                if (($m['role'] ?? null) === 'system') {
                    continue;
                                $contents[] = [
                    'role' => ($m['role'] ?? 'user') === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => (string) ($m['content'] ?? '')]],
                ];
            }

            $apiKey = config('services.gemini.key');

            $geminiCall = function (int $maxTokens) use ($contents, $apiKey) {
                return Http::timeout(45)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key={$apiKey}", [
                        'system_instruction' => [
                            'parts' => [['text' => $this->systemPrompt()]],
                        ],
                        'contents' => $contents,
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'maxOutputTokens' => $maxTokens,
                            // This is a short customer-support chatbot — it doesn't need
                            // chain-of-thought, and thinking tokens were eating the whole
                            // output budget and leaving the answer blank. Turn it off so
                            // every token goes to the actual reply.
                            'thinkingConfig' => ['thinkingBudget' => 0],
                        ],
                    ]);
            };

            $response = $geminiCall(1500);

            // A temporary Gemini server error (5xx, e.g. "model overloaded")
            // usually clears in a second — retry once before showing a glitch.
            if ($response->serverError()) {
                usleep(800000);
                $response = $geminiCall(1500);
            }

            if ($response->successful()) {
                $finishReason = $response->json('candidates.0.finishReason');

                // If Gemini cut the reply short because it ran out of its
                // token budget mid-sentence, retry once with a bigger
                // budget rather than showing the guest a half-finished
                // answer. Any other non-STOP reason (SAFETY, RECITATION,
                // OTHER) can't be fixed by raising the token limit, so we
                // just log it for later review instead of retrying blindly.
                if ($finishReason === 'MAX_TOKENS') {
                    Log::warning('Wonder Park chat reply hit MAX_TOKENS, retrying with a bigger budget', [
                        'partial_reply' => $response->json('candidates.0.content.parts.0.text'),
                    ]);
                    $response = $geminiCall(2500);
                } elseif ($finishReason && $finishReason !== 'STOP') {
                    Log::warning('Wonder Park chat reply stopped for a non-STOP reason', [
                        'finish_reason' => $finishReason,
                        'partial_reply' => $response->json('candidates.0.content.parts.0.text'),
                    ]);
                }
            }
            Log::info('Wonder Park chat DEBUG', ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 800)]);

            if ($response->failed()) {
            if ($response->failed()) {
                $status = $response->status();
                $errorCode = $response->json('error.status');

                Log::error('Wonder Park chat Gemini error', [
                    'status' => $status,
                    'error_code' => $errorCode,
                    'body' => $response->body(),
                ]);

                if ($status === 429 || $errorCode === 'RESOURCE_EXHAUSTED') {
                    return response()->json([
                        'reply' => "Sorry, the Wonder Park chatbot is a bit busy right now (usage limit reached). Please try again in a bit.",
                        'error' => true,
                    ], 200)->cookie('reks_chat_session', $sessionId, 60 * 24 * 30);
                }

                return response()->json([
                    'reply' => "Sorry, I ran into a glitch. Please try again in a moment.",
                    'error' => true,
                ], 200)->cookie('reks_chat_session', $sessionId, 60 * 24 * 30);
            }

            $replyText = $response->json('candidates.0.content.parts.0.text');
            $reply = (is_string($replyText) && trim($replyText) !== '')
                ? $replyText
                : "Sorry, I don't have an answer right now. Please try again?";

            // Safety net: strip any markdown bold/italic symbols the model
            // might still slip in, since the chat bubble renders plain text
            // only (textContent, not innerHTML) — leftover ** or __ would
            // otherwise show up literally to the guest.
            $reply = preg_replace('/(\*\*|__)(.*?)\1/', '$2', $reply);
            $reply = str_replace(['**', '__'], '', $reply);

            ChatLog::create([
                'session_id' => $sessionId,
                'user_message' => $request->input('message'),
                'ai_reply' => $reply,
                'escalated' => false,
            ]);

            return response()->json(['reply' => $reply, 'error' => false])
                ->cookie('reks_chat_session', $sessionId, 60 * 24 * 30);
        } catch (\Throwable $e) {
            Log::error('Wonder Park chat exception', [
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => "Oops, we're having a connection issue. Please try again in a moment.",
                'error' => true,
            ], 200)->cookie('reks_chat_session', $sessionId, 60 * 24 * 30);
        }
    }

}
