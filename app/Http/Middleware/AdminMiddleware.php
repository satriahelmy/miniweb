<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please login to access this page.');
        }

        if (auth()->user()->role !== 'admin') {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}

