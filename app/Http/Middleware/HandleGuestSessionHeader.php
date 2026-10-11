<?php

namespace App\Http\Middleware;

use App\Models\GuestSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleGuestSessionHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guestSessionId = $request->header('X-Guest-Session-Id');

        if ($guestSessionId && is_string($guestSessionId) && preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $guestSessionId)) {
            // Ensure guest session record exists
            try {
                GuestSession::findOrCreateSession($guestSessionId);
            } catch (\Throwable $e) {
                // Ignore DB failure on guest session creation to avoid breaking request
            }

            // Expose guest_session_id on request attributes
            $request->attributes->set('guest_session_id', $guestSessionId);
        }

        $response = $next($request);

        // Echo back guest session ID header if available
        if ($guestSessionId) {
            $response->headers->set('X-Guest-Session-Id', $guestSessionId);
        }

        return $response;
    }
}
