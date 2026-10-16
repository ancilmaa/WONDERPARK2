<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiPromoController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question' => 'required|string|max:400',
            'context'  => 'nullable|array',
        ]);

        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return response()->json(['message' => 'AI is not configured.'], 500);
        }

        $system = <<<'TXT'
You are a promo and marketing assistant for a theme park (Lipa Branch) with these attractions: Dino Lakbay, Skataya, Piyesta Rides.
You will receive the park's real online-booking data as JSON (bookings, share %, top age group, top packages, top visitor origins).
Rules:
- Answer in Taglish (Filipino-English mix), friendly and short (under 150 words).
- Base every suggestion on the given data. If the data is not enough, say so honestly. Never invent numbers.
- When asked for promos, give 2-3 concrete ideas, each with a catchy name, what the offer is, and why it fits that age group or attraction.
- Plain text only. Use "- " for bullets and **bold** for promo names. No headings, no tables.
- Only answer questions about promos, age groups, bookings and marketing for this park. Politely decline anything else.
TXT;

        $userMessage = "Booking data:\n"
            . json_encode($data['context'] ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            . "\n\nQuestion: " . $data['question'];

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => config('services.anthropic.model', 'claude-sonnet-5-5'),
                'max_tokens' => 600,
                'system'     => $system,
                'messages'   => [['role' => 'user', 'content' => $userMessage]],
            ]);

            if (! $response->successful()) {
                Log::warning('AI promo request failed', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['message' => 'AI request failed.'], 502);
            }

            $answer = collect($response->json('content', []))
                ->where('type', 'text')->pluck('text')->implode("\n");

            return response()->json(['answer' => trim($answer)]);
        } catch (\Throwable $e) {
            Log::error('AI promo error: ' . $e->getMessage());
            return response()->json(['message' => 'AI request failed.'], 502);
        }
    }
}
