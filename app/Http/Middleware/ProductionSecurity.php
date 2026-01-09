<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductionSecurity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && config('app.debug')) {
            abort(500, 'Debug mode must be disabled in production.');
        }

        return $next($request);
    }
}
