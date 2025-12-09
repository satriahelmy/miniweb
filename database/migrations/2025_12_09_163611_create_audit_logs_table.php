<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // login, logout, file_upload, file_download, file_delete, submission_create, etc.
            $table->string('model_type')->nullable(); // App\Models\File, App\Models\Submission, etc.
            $table->unsignedBigInteger('model_id')->nullable(); // ID dari model terkait
            $table->string('ip_address', 45)->index(); // IPv6 support
            $table->string('user_agent')->nullable();
            $table->enum('status', ['success', 'failed', 'blocked', 'unauthorized'])->default('success');
            $table->text('description')->nullable(); // Detail action
            $table->json('metadata')->nullable(); // Additional data (file name, file size, etc.)
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
