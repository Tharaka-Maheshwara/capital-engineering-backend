<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GoogleAuthRequest;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class GoogleAuthController extends Controller
{
    /**
     * Handle Google Sign-In by verifying the ID token and creating/finding the user.
     */
    public function handleGoogleAuth(GoogleAuthRequest $request): JsonResponse|RedirectResponse
    {
        $idToken = $request->string('id_token')->toString();
        $redirectUri = rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/') . '/auth/google/callback';

        // Verify the ID token with Google
        $googlePayload = $this->verifyGoogleToken($idToken);

        if ($googlePayload === null) {
            return $this->redirectWithError($redirectUri, 'Invalid Google token. Please try again.');
        }

        $googleId = $googlePayload['sub'] ?? null;
        $email = $googlePayload['email'] ?? null;
        $name = $googlePayload['name'] ?? '';
        $avatar = $googlePayload['picture'] ?? null;
        $emailVerified = $googlePayload['email_verified'] ?? false;

        if (!$googleId || !$email) {
            return $this->redirectWithError($redirectUri, 'Could not retrieve your Google account information.');
        }

        if (!$emailVerified) {
            return $this->redirectWithError($redirectUri, 'Your Google email address is not verified.');
        }

        // Find existing user by google_id or email
        $user = User::where('google_id', $googleId)->first();

        if (!$user) {
            $user = User::where('email', $email)->first();
        }

        $isNewUser = false;

        if ($user) {
            // Link Google account if not already linked
            if ($user->google_id === null) {
                $user->forceFill([
                    'google_id' => $googleId,
                    'avatar' => $avatar ?? $user->avatar,
                ])->save();
            }
        } else {
            // Create new user from Google profile
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $avatar,
                'password' => null,
                'role' => 'user',
            ]);

            $isNewUser = true;
        }

        $token = $user->issueApiToken();

        $userPayload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
            'created_at' => $user->created_at?->toISOString(),
        ];

        $query = http_build_query([
            'token' => $token,
            'user' => json_encode($userPayload, JSON_UNESCAPED_SLASHES),
            'message' => $isNewUser
                ? 'Account created successfully with Google.'
                : 'Signed in successfully with Google.',
        ]);

        return redirect()->away($redirectUri . '?' . $query);
    }

    /**
     * Verify a Google ID token and return the payload if valid.
     */
    private function verifyGoogleToken(string $idToken): ?array
    {
        $clientId = config('services.google.client_id');

        if (!$clientId) {
            Log::error('GOOGLE_CLIENT_ID is not configured in services.google.client_id');
            return null;
        }

        try {
            $client = new GoogleClient(['client_id' => $clientId]);
            $payload = $client->verifyIdToken($idToken);

            if (!$payload) {
                Log::warning('Google ID token verification returned null/false.');
                return null;
            }

            return $payload;
        } catch (\Throwable $e) {
            Log::error('Google token verification failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return null;
        }
    }

    private function redirectWithError(string $redirectUri, string $message): RedirectResponse
    {
        return redirect()->away($redirectUri . '?' . http_build_query([
            'error' => $message,
        ]));
    }
}
