<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductionSecurity
{
    /**
     * Handle an incoming request.
     * Ensure production security settings
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // In production, ensure debug mode is disabled
        if (app()->environment('production') && config('app.debug')) {
            abort(500, 'Debug mode must be disabled in production.');
        }

        return $next($request);
    }
}
