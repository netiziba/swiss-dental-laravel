<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:120',
            'email'           => 'required|email:rfc,dns|max:190',
            'phone'           => 'nullable|string|max:40',
            'message'         => 'required|string|min:5|max:5000',
            'recaptcha_token' => 'nullable|string',
        ]);

        if (! $this->verifyRecaptcha($request->ip(), $data['recaptcha_token'] ?? null)) {
            return response()->json(['message' => 'reCAPTCHA-Prüfung fehlgeschlagen.'], 422);
        }

        $recipient = config('mail.contact_to') ?: config('mail.from.address');
        if (! is_string($recipient) || trim($recipient) === '') {
            Log::error('Kontaktformular: Kein Empfänger konfiguriert.');

            return response()->json(['message' => 'Kontaktformular ist aktuell nicht verfügbar.'], 503);
        }

        $subject = 'Neue Kontaktanfrage: ' . $data['name'];
        $body = implode("\n", [
            'Neue Kontaktanfrage von der Website',
            '',
            'Name: ' . $data['name'],
            'E-Mail: ' . $data['email'],
            'Telefon: ' . ($data['phone'] ?: '—'),
            '',
            'Nachricht:',
            $data['message'],
            '',
            'IP: ' . ($request->ip() ?: 'unbekannt'),
            'Zeit: ' . now()->toDateTimeString(),
        ]);

        Mail::raw($body, function ($message) use ($recipient, $subject, $data) {
            $message->to($recipient)->subject($subject)->replyTo($data['email'], $data['name']);
        });

        return response()->json(['message' => 'Nachricht gesendet.']);
    }

    private function verifyRecaptcha(?string $ip, ?string $token): bool
    {
        $secret = (string) config('services.recaptcha.secret');
        if ($secret === '') {
            // Allow submissions when no server-side secret is configured.
            return true;
        }
        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        try {
            $res = Http::asForm()->timeout(10)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => $ip,
            ]);
            $ok = (bool) data_get($res->json(), 'success', false);
            $score = (float) data_get($res->json(), 'score', 1.0);

            return $ok && $score >= 0.3;
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verify failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}

