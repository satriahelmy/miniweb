<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $fillable = [
        'ip_address',
        'email',
        'success',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    /**
     * Check if IP address has exceeded max attempts
     */
    public static function isBlocked(string $ipAddress, int $maxAttempts = 5, int $lockoutMinutes = 15): bool
    {
        $recentAttempts = self::where('ip_address', $ipAddress)
            ->where('attempted_at', '>=', now()->subMinutes($lockoutMinutes))
            ->where('success', false)
            ->count();

        return $recentAttempts >= $maxAttempts;
    }

    /**
     * Get remaining lockout time in minutes
     */
    public static function getRemainingLockoutTime(string $ipAddress, int $lockoutMinutes = 15): int
    {
        $lastFailedAttempt = self::where('ip_address', $ipAddress)
            ->where('success', false)
            ->orderBy('attempted_at', 'desc')
            ->first();

        if (!$lastFailedAttempt) {
            return 0;
        }

        $lockoutUntil = $lastFailedAttempt->attempted_at->addMinutes($lockoutMinutes);
        $remaining = now()->diffInMinutes($lockoutUntil, false);

        return max(0, $remaining);
    }

    /**
     * Record a login attempt
     */
    public static function record(string $ipAddress, ?string $email, bool $success): void
    {
        self::create([
            'ip_address' => $ipAddress,
            'email' => $email,
            'success' => $success,
            'attempted_at' => now(),
        ]);
    }

    /**
     * Clear successful attempts for IP (after successful login)
     */
    public static function clearFailedAttempts(string $ipAddress): void
    {
        self::where('ip_address', $ipAddress)
            ->where('success', false)
            ->delete();
    }
}
