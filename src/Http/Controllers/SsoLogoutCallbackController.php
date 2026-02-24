<?php

namespace Mixu\SSOAuth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SsoLogoutCallbackController
{
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-SSO-Signature');
        $payload = $request->getContent();

        if (! $signature || ! $payload) {
            Log::warning('Global logout webhook: missing signature or payload');
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $secret = config('services.mixuauth.webhook_secret');
        if (! $secret) {
            Log::warning('Global logout webhook: SSO_WEBHOOK_SECRET not configured');
            return response()->json(['error' => 'Not configured'], 500);
        }

        $expected = hash_hmac('sha256', $payload, $secret);
        if (! hash_equals($expected, $signature)) {
            Log::warning('Global logout webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = json_decode($payload, true);
        if (($data['event'] ?? '') !== 'global_logout') {
            return response()->json(['error' => 'Unknown event'], 400);
        }

        $ssoUserId = $data['user_id'] ?? null;
        $email = $data['email'] ?? null;

        if (! $ssoUserId && ! $email) {
            Log::warning('Global logout webhook: missing user_id and email');
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $ssoUserId = $ssoUserId !== null ? (string) $ssoUserId : null;

        $this->invalidateSessionsForUser($ssoUserId, $email);

        return response()->json(['success' => true]);
    }

    /**
     * Hapus semua session yang berisi sso_user dengan user_id atau email yang cocok.
     * Session disimpan di tabel sessions dengan payload (serialized/encrypted).
     */
    protected function invalidateSessionsForUser(?string $ssoUserId, ?string $email): void
    {
        $table = config('session.table', 'sessions');
        $connection = config('session.connection') ?: config('database.default');
        $encrypt = config('session.encrypt', false);

        $sessions = DB::connection($connection)->table($table)->get();

        foreach ($sessions as $session) {
            try {
                // Laravel DatabaseSessionHandler menyimpan payload sebagai base64
                $payload = base64_decode((string) $session->payload, true);
                if ($payload === false) {
                    continue;
                }

                if ($encrypt) {
                    $payload = Crypt::decrypt($payload);
                }

                $decoded = @unserialize($payload);
                if (! is_array($decoded)) {
                    continue;
                }

                $ssoUser = $decoded['sso_user'] ?? null;
                if (! is_array($ssoUser)) {
                    continue;
                }

                $sessionSsoId = isset($ssoUser['id']) ? (string) $ssoUser['id'] : null;
                $sessionEmail = $ssoUser['email'] ?? null;

                $match = false;
                if ($ssoUserId && $sessionSsoId === $ssoUserId) {
                    $match = true;
                }
                if ($email && $sessionEmail && strcasecmp($sessionEmail, $email) === 0) {
                    $match = true;
                }

                if ($match) {
                    DB::connection($connection)->table($table)->where('id', $session->id)->delete();
                    Log::info('Global logout: invalidated session', ['session_id' => $session->id]);
                }
            } catch (\Throwable $e) {
                Log::debug('Global logout: skip session decode error', [
                    'session_id' => $session->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
