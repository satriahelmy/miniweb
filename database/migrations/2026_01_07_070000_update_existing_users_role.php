<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing users that don't have a role set
        DB::table('users')
            ->whereNull('role')
            ->orWhere('role', '')
            ->update(['role' => 'user']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this
    }
};

