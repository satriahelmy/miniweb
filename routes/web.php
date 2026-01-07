<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1'); // Max 5 requests per minute
    
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Rate limiting: Max 5 login attempts per IP per minute
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Submissions routes
    Route::resource('submissions', SubmissionController::class);

    // Files routes
    Route::resource('files', FileController::class)->except(['show', 'update', 'edit']);
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');

    // Admin routes (require admin role)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'index'])->name('users.index');
        Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleStatus'])->name('users.toggle-status');
    });
});
