<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionCookie
{
    /**
     * Handle an incoming request.
     *
     * Isolate the session cookie name for admin subdomain and admin routes to prevent
     * session collisions with the public storefront running on localhost/127.0.0.1.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (str_starts_with($host, 'admin.') || $request->is('admin*')) {
            config(['session.cookie' => 'eclore_admin_session']);
        }

        return $next($request);
    }
}
