<?php

namespace App\Providers;

use App\Models\File;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Scoped route model binding for File - only allow access to user's own files
        Route::bind('file', function ($value) {
            $file = File::findOrFail($value);
            
            // Additional security: ensure file belongs to authenticated user
            if (Auth::check() && $file->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access. You can only access your own files.');
            }
            
            return $file;
        });

        // Scoped route model binding for Submission - only allow access to user's own submissions
        Route::bind('submission', function ($value) {
            $submission = Submission::findOrFail($value);
            
            // Additional security: ensure submission belongs to authenticated user
            if (Auth::check() && $submission->user_id !== Auth::id()) {
                abort(403, 'Unauthorized access. You can only access your own submissions.');
            }
            
            return $submission;
        });
    }
}
