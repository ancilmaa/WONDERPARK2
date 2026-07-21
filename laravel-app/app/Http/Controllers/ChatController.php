<?php

namespace App\Http\Controllers;

use App\Models\ChatLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private function systemPrompt(): string
    {
        return <<<PROMPT
You are the "Wonder Park" chatbot, the friendly AI assistant for REKS
Amusement Park's online booking system. Always answer in clear, simple
English only — regardless of what language the guest writes in — so every
visitor can understand you. Keep answers brief (2-4 sentences) and warm.

FORMATTING RULES (important):
- Never use markdown formatting — no asterisks for bold/italics, no bullet
  points, no headers. The chat widget only displays plain text, so any
  markdown symbols would show up literally (e.g. the guest would see
  "**Dino Adventure**" with the asterisks still there). Write in plain,
  natural sentences only.
- Whenever you mention a package, always include its starting price and
  the pax (guest count) it starts at, the same way you would for any
  other package — don't describe one package with numbers and another
  without. If you don't have an exact figure for something, say so rather
  than leaving it vague.

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

THE THREE EXPERIENCES AND THEIR PACKAGES:

1. Dino Adventure (Kids Party & Softplay):
   - Non-Exclusive - Shared Play Area: shared softplay + party area, 1-hr
     Dino mascot appearance. Starts at 15,000 PHP (10 pax), up to 30 pax.
   - Exclusive - Private Party (weekdays, Mon-Fri): private use of the
     whole play area. Starts at 62,000 PHP (40 pax), up to 80 pax.
   - Exclusive - Private Party (weekends & holidays): same as above but
     for Sat/Sun/holidays. Starts at 67,000 PHP (40 pax), up to 80 pax.
   - Walk-in Play Passes (no party, just softplay access per guest):
     1 Hour from 299 PHP/guest, 2 Hours from 399 PHP/guest, All Day from
     599 PHP/guest.

2. RollerFever (Skate Rink Parties):
   - Weekday Rates: skate rink party packages, starts at 11,994 PHP for
     10 guests, up to 45 guests.
   - Weekend Rates: same idea for Sat-Sun, starts at 15,600 PHP for 10
     guests, up to 45 guests.
   - Walk-in Skate Passes (per guest, no party): 1 Hour from 249 PHP,
     2 Hours from 399 PHP, All Day from 599 PHP.
   - Group Bundle (4+1 free, good for barkada/family groups): 1 Hour for
     996 PHP total (5 guests, 1 free), 2 Hours for 1,596 PHP total.

3. Field of Rides (Amusement Rides & Games) - individual carnival ride
   tickets, priced per head:
   - Try Every Ride - All Access Pass: one ride each on every attraction
     (Tiger Train, Mini Carousel, Star Speed, Little Chicken, Boat Pool,
     Carousel, Flying Chair, Mini Ferris Wheel, Samba Baloon, Crazy Plane,
     Vikings, Go-Kart) for 600 PHP per head - the best value option.
   - Individual ride tickets are grouped by price: 60 PHP/head rides
     (Tiger Train, Mini Carousel, Star Speed, Little Chicken, Boat Pool,
     Carousel, Flying Chair, Mini Ferris Wheel, Samba Baloon, Crazy
     Plane), 120 PHP/head rides (Vikings, Go-Kart), and 150 PHP/head
     rides (Inflatable Playground, Mini Trampoline, Rev & Roll, Happy
     Cars, Jurassic Adventure).
   - Guests under 4ft must be accompanied by a paying guardian, and
     riders should be free of motion sickness, heart conditions, or other
     health restrictions listed at the ticket booth.

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

Keep answers short and conversational — this is a chat widget, not an essay.
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
                if ($m['role'] === 'system') {
                    continue;
                }
                $contents[] = [
                    'role' => $m['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $m['content']]],
                ];
            }

            $apiKey = config('services.gemini.key');

            $geminiCall = function (int $maxTokens) use ($contents, $apiKey) {
                return Http::timeout(20)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                        'system_instruction' => [
                            'parts' => [['text' => $this->systemPrompt()]],
                        ],
                        'contents' => $contents,
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'maxOutputTokens' => $maxTokens,
                        ],
                    ]);
            };

            $response = $geminiCall(500);

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
                    $response = $geminiCall(800);
                } elseif ($finishReason && $finishReason !== 'STOP') {
                    Log::warning('Wonder Park chat reply stopped for a non-STOP reason', [
                        'finish_reason' => $finishReason,
                        'partial_reply' => $response->json('candidates.0.content.parts.0.text'),
                    ]);
                }
            }

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

            $reply = $response->json('candidates.0.content.parts.0.text', "Sorry, I don't have an answer right now. Please try again?");

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
