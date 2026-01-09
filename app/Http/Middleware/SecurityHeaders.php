<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remove X-Powered-By header (security best practice)
        $response->headers->remove('X-Powered-By');

        // Content Security Policy (CSP) - Prevent XSS attacks
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data:; " .
               "font-src 'self' data:; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self'; " .
               "object-src 'none'; " .
               "upgrade-insecure-requests";

        $response->headers->set('Content-Security-Policy', $csp);

        // X-Frame-Options - Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-Content-Type-Options - Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection - Enable browser XSS filter (legacy, but still useful)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy - Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy - Control browser features
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}

