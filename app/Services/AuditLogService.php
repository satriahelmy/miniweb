<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * Log an action
     */
    public static function log(
        string $action,
        string $status = 'success',
        ?Model $model = null,
        ?string $description = null,
        ?array $metadata = null,
        ?Request $request = null
    ): void {
        try {
            $request = $request ?? request();
            
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model?->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => $status,
                'description' => $description,
                'metadata' => $metadata,
            ]);

            // Also log to Laravel log file for critical events
            if (in_array($status, ['failed', 'blocked', 'unauthorized'])) {
                Log::warning("Audit Log: {$action} - {$status}", [
                    'user_id' => Auth::id(),
                    'ip' => $request->ip(),
                    'description' => $description,
                ]);
            }
        } catch (\Exception $e) {
            // Don't break the application if logging fails
            Log::error('Failed to create audit log', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log authentication events
     */
    public static function logAuth(string $action, string $status, ?string $email = null, ?string $reason = null): void
    {
        self::log(
            action: "auth.{$action}",
            status: $status,
            description: $reason ?? "User {$action}: {$email}",
            metadata: ['email' => $email]
        );
    }

    /**
     * Log file operations
     */
    public static function logFile(string $action, Model $file, string $status = 'success', ?string $reason = null): void
    {
        self::log(
            action: "file.{$action}",
            status: $status,
            model: $file,
            description: $reason ?? "File {$action}: {$file->original_name}",
            metadata: [
                'file_name' => $file->original_name,
                'file_size' => $file->size,
                'mime_type' => $file->mime_type,
            ]
        );
    }

    /**
     * Log submission operations
     */
    public static function logSubmission(string $action, Model $submission, string $status = 'success'): void
    {
        self::log(
            action: "submission.{$action}",
            status: $status,
            model: $submission,
            description: "Submission {$action}: {$submission->title}",
            metadata: [
                'title' => $submission->title,
                'content_length' => strlen($submission->content),
            ]
        );
    }

    /**
     * Log authorization violations
     */
    public static function logUnauthorized(string $action, ?Model $model = null, ?string $reason = null): void
    {
        self::log(
            action: "unauthorized.{$action}",
            status: 'unauthorized',
            model: $model,
            description: $reason ?? "Unauthorized access attempt: {$action}",
            metadata: [
                'attempted_resource' => $model ? get_class($model) : null,
                'resource_id' => $model?->id,
            ]
        );
    }
}

