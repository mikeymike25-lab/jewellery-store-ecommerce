<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SessionWishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Issue a personal access token for an authenticated user (POST /api/v1/auth/token).
     */
    public function token(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'sometimes|string',
            'email' => 'sometimes|string',
            'username' => 'sometimes|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $login = $request->input('login') ?: $request->input('email') ?: $request->input('username');

        if (! $login) {
            return response()->json([
                'success' => false,
                'message' => 'Email or username is required',
            ], 422);
        }

        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Migrate guest cart and wishlist if guest session ID is provided
        $guestSessionId = $request->header('X-Guest-Session-Id') ?: $request->input('guest_session_id');
        if ($guestSessionId) {
            try {
                $cartController = new CartController;
                $cartController->migrateCartToUser($user->id, $guestSessionId);

                $wishlistService = app(SessionWishlistService::class);
                $wishlistService->migrateGuestToUser($user->id, $guestSessionId);
            } catch (\Throwable $e) {
                \Log::warning('Guest data migration on login failed: '.$e->getMessage());
            }
        }

        $deviceName = $request->input('device_name', 'eclore-mobile-app');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Authenticated successfully',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'is_admin' => (bool) $user->is_admin,
            ],
        ]);
    }

    /**
     * Revoke current access token (POST /api/v1/auth/logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user profile (GET /api/v1/auth/user).
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $fullName = $user->name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return response()->json([
            'success' => true,
            'user' => array_merge($user->toArray(), [
                'name' => $fullName ?: ($user->username ?? 'Client'),
            ]),
        ]);
    }

    /**
     * Register a new user and issue token (POST /api/v1/auth/register).
     */
    public function register(Request $request): JsonResponse
    {
        $input = $request->all();

        // Support both camelCase and snake_case inputs
        if (empty($input['first_name']) && !empty($input['firstName'])) {
            $input['first_name'] = $input['firstName'];
        }
        if (empty($input['last_name']) && !empty($input['lastName'])) {
            $input['last_name'] = $input['lastName'];
        }
        if (empty($input['password_confirmation']) && !empty($input['confirmPassword'])) {
            $input['password_confirmation'] = $input['confirmPassword'];
        }
        if (empty($input['first_name']) && !empty($input['name'])) {
            $parts = explode(' ', trim($input['name']), 2);
            $input['first_name'] = $parts[0];
            $input['last_name'] = $parts[1] ?? 'Client';
        }
        if (!empty($input['username']) && str_contains($input['username'], '@')) {
            $input['username'] = strstr($input['username'], '@', true);
        }
        if (!empty($input['username'])) {
            $input['username'] = preg_replace('/[^a-zA-Z0-9_]/', '', $input['username']);
        }

        if (empty($input['username']) && !empty($input['email'])) {
            $baseUser = strstr($input['email'], '@', true);
            $cleanUser = preg_replace('/[^a-zA-Z0-9_]/', '', $baseUser) ?: 'client';
            $uniqueUser = $cleanUser;
            $counter = 1;
            while (User::where('username', $uniqueUser)->exists()) {
                $uniqueUser = $cleanUser . '_' . $counter++;
            }
            $input['username'] = $uniqueUser;
        }

        $validator = Validator::make($input, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'username' => 'required|string|min:3|max:30|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'username' => $input['username'],
            'password' => Hash::make($input['password']),
            'provider' => 'local',
        ]);

        // Migrate guest cart and wishlist if guest session ID is provided
        $guestSessionId = $request->header('X-Guest-Session-Id') ?: $request->input('guest_session_id');
        if ($guestSessionId) {
            try {
                $cartController = new CartController;
                $cartController->migrateCartToUser($user->id, $guestSessionId);

                $wishlistService = app(SessionWishlistService::class);
                $wishlistService->migrateGuestToUser($user->id, $guestSessionId);
            } catch (\Throwable $e) {
                \Log::warning('Guest data migration on registration failed: '.$e->getMessage());
            }
        }

        $deviceName = $request->input('device_name', 'eclore-mobile-app');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'is_admin' => (bool) $user->is_admin,
            ],
        ], 201);
    }

    /**
     * Handle forgot password request and dispatch reset link (POST /api/v1/auth/forgot-password).
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => 'sometimes|string',
            'email' => 'sometimes|string',
            'username' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $login = $request->input('login') ?: $request->input('email') ?: $request->input('username');

        if (! $login) {
            return response()->json([
                'success' => false,
                'message' => 'Email or username is required',
            ], 422);
        }

        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email or username.',
            ], 404);
        }

        try {
            $magicLinkService = app(\App\Services\MagicLinkService::class);
            $token = $magicLinkService->generateMagicLink($user, 'password_reset');

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your registered email address.',
                'user_email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to dispatch password reset email: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch password reset email. Please try again.',
            ], 500);
        }
    }

    /**
     * Reset password using verification token (POST /api/v1/auth/reset-password).
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $magicLinkService = app(\App\Services\MagicLinkService::class);
        $tokenRecord = $magicLinkService->verifyMagicLink($request->token, 'password_reset');

        if (! $tokenRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired password reset link.',
            ], 400);
        }

        $user = User::where('email', $tokenRecord->email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User associated with this token was not found.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You may now sign in with your new credentials.',
        ]);
    }

    /**
     * Mobile / API Google OAuth Redirect URL
     */
    public function googleRedirect(Request $request)
    {
        try {
            $redirectUrl = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'url' => $redirectUrl]);
            }

            return redirect($redirectUrl);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication service is unavailable: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / API Google OAuth Callback
     */
    public function googleCallback(Request $request)
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')->stateless()->user();
            $existingUser = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($existingUser) {
                if (! $existingUser->google_id) {
                    $existingUser->update([
                        'google_id' => $googleUser->id,
                        'provider' => 'google',
                    ]);
                }
                $user = $existingUser;
            } else {
                $nameParts = explode(' ', trim($googleUser->name ?? 'Éclore Patron'));
                $user = User::create([
                    'first_name' => $nameParts[0] ?? 'Patron',
                    'last_name' => implode(' ', array_slice($nameParts, 1)) ?: 'Maison',
                    'email' => $googleUser->email,
                    'username' => 'client_'.substr(md5($googleUser->id.time()), 0, 8),
                    'google_id' => $googleUser->id,
                    'provider' => 'google',
                    'email_verified_at' => now(),
                ]);
            }

            // Migrate guest cart and wishlist
            $guestSessionId = $request->header('X-Guest-Session-Id') ?: $request->input('guest_session_id');
            if ($guestSessionId) {
                try {
                    $cartController = new CartController;
                    $cartController->migrateCartToUser($user->id, $guestSessionId);
                    $wishlistService = app(SessionWishlistService::class);
                    $wishlistService->migrateGuestToUser($user->id, $guestSessionId);
                } catch (\Throwable $e) {
                    \Log::warning('Guest migration during Google OAuth failed: '.$e->getMessage());
                }
            }

            $token = $user->createToken('ionic-google-oauth')->plainTextToken;

            // Support direct JSON response if explicitly requesting JSON
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'user' => $user,
                ]);
            }

            return redirect($frontendUrl.'/tabs/account?token='.$token);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google authentication failed: '.$e->getMessage(),
                ], 400);
            }

            return redirect($frontendUrl.'/tabs/account?error='.urlencode($e->getMessage()));
        }
    }
}
