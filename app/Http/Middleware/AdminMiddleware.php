<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please login to access this page.');
        }

        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}

