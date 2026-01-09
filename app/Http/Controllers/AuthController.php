<?php

namespace App\Http\Controllers;

use App\Helpers\RedirectHelper;
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
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
        ]);

        Auth::login($user);
        AuditLogService::logAuth('register', 'success', $user->email);

        return redirect()->route('dashboard')->with('success', 'Registration successful!');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $ipAddress = $request->ip();
        $maxAttempts = 3;
        $lockoutMinutes = 15;
        if (LoginAttempt::isBlocked($ipAddress, $maxAttempts, $lockoutMinutes)) {
            $remainingTime = LoginAttempt::getRemainingLockoutTime($ipAddress, $lockoutMinutes);
            
            AuditLogService::logAuth('login', 'blocked', $credentials['email'] ?? null, "IP blocked: {$ipAddress}");
            
            return back()->withErrors([
                'email' => "Too many failed login attempts. Please try again in {$remainingTime} minutes.",
            ])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && !$user->is_active) {
            LoginAttempt::record($ipAddress, $credentials['email'], false);
            AuditLogService::logAuth('login', 'failed', $credentials['email'], 'Account is disabled');
            
            return back()->withErrors([
                'email' => 'Your account has been disabled. Please contact administrator.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            LoginAttempt::record($ipAddress, $credentials['email'], true);
            LoginAttempt::clearFailedAttempts($ipAddress);
            AuditLogService::logAuth('login', 'success', $credentials['email']);
            $request->session()->regenerate();

            $intendedUrl = RedirectHelper::safeIntended($request, 'dashboard');
            return redirect($intendedUrl)->with('success', 'Login successful!');
        }

        LoginAttempt::record($ipAddress, $credentials['email'], false);
        AuditLogService::logAuth('login', 'failed', $credentials['email'], 'Invalid credentials');
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

    public function logout(Request $request)
    {
        AuditLogService::logAuth('logout', 'success', Auth::user()?->email);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
