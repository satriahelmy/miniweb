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
        // Note: 'unsafe-inline' is needed for Laravel's CSRF token meta tag and inline scripts
        // For production, consider using nonce-based CSP for better security
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline'; " . // Allow inline scripts for Laravel (CSRF token, etc.)
               "style-src 'self' 'unsafe-inline'; " . // Allow inline styles
               "img-src 'self' data:; " . // Allow images from self and data URIs only (no wildcard)
               "font-src 'self' data:; " . // Allow fonts from self and data URIs
               "connect-src 'self'; " . // Allow AJAX/fetch to same origin
               "frame-ancestors 'none'; " . // Prevent clickjacking (alternative to X-Frame-Options)
               "base-uri 'self'; " . // Restrict base tag
               "form-action 'self'; " . // Restrict form submissions
               "object-src 'none'; " . // Disable plugins
               "upgrade-insecure-requests"; // Upgrade HTTP to HTTPS

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

