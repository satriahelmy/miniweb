<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(Request $request)
    {
        // Input validation
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()      // Harus ada huruf besar dan kecil
                    ->numbers()        // Harus ada angka
                    ->symbols(),       // Harus ada karakter khusus
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create user with hashed password (Laravel automatically hashes using bcrypt)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Password hashing dengan bcrypt
            'role' => 'user', // Default role for new users
            'is_active' => true, // New users are active by default
        ]);

        // Auto login after registration
        Auth::login($user);

        // Log registration
        AuditLogService::logAuth('register', 'success', $user->email);

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login with rate limiting and IP blocking
     */
    public function login(Request $request)
    {
        $ipAddress = $request->ip();
        $maxAttempts = 3; // Maximum failed attempts
        $lockoutMinutes = 15; // Lockout duration in minutes

        // Check if IP is blocked due to too many failed attempts
        if (LoginAttempt::isBlocked($ipAddress, $maxAttempts, $lockoutMinutes)) {
            $remainingTime = LoginAttempt::getRemainingLockoutTime($ipAddress, $lockoutMinutes);
            
            // Log blocked login attempt
            AuditLogService::logAuth('login', 'blocked', $credentials['email'] ?? null, "IP blocked: {$ipAddress}");
            
            return back()->withErrors([
                'email' => "Too many failed login attempts. Please try again in {$remainingTime} minutes.",
            ])->onlyInput('email');
        }

        // Input validation
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && !$user->is_active) {
            LoginAttempt::record($ipAddress, $credentials['email'], false);
            AuditLogService::logAuth('login', 'failed', $credentials['email'], 'Account is disabled');
            
            return back()->withErrors([
                'email' => 'Your account has been disabled. Please contact administrator.',
            ])->onlyInput('email');
        }

        // Attempt login with secure session
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Record successful login attempt
            LoginAttempt::record($ipAddress, $credentials['email'], true);
            
            // Clear failed attempts for this IP
            LoginAttempt::clearFailedAttempts($ipAddress);
            
            // Log successful login
            AuditLogService::logAuth('login', 'success', $credentials['email']);
            
            $request->session()->regenerate(); // Regenerate session ID for security

            return redirect()->intended(route('dashboard'))->with('success', 'Login successful!');
        }

        // Record failed login attempt
        LoginAttempt::record($ipAddress, $credentials['email'], false);
        
        // Log failed login
        AuditLogService::logAuth('login', 'failed', $credentials['email'], 'Invalid credentials');

        // Check remaining attempts
        $failedAttempts = LoginAttempt::where('ip_address', $ipAddress)
            ->where('attempted_at', '>=', now()->subMinutes($lockoutMinutes))
            ->where('success', false)
            ->count();

        $remainingAttempts = $maxAttempts - $failedAttempts;

        $errorMessage = 'The provided credentials do not match our records.';
        if ($remainingAttempts > 0 && $remainingAttempts <= 3) {
            $errorMessage .= " {$remainingAttempts} attempt(s) remaining.";
        }

        return back()->withErrors([
            'email' => $errorMessage,
        ])->onlyInput('email');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        // Log logout before logging out
        AuditLogService::logAuth('logout', 'success', Auth::user()?->email);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
