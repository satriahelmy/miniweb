<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * SECURITY NOTE: Default users are only created in non-production environments.
     * For production, create users manually or use a different seeder.
     */
    public function run(): void
    {
        // Only create default users in development/testing environments
        // NEVER create default users in production!
        if (app()->environment(['local', 'testing', 'development'])) {
            // Create default admin user (only if doesn't exist)
            User::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin',
                    'password' => Hash::make('password123'), // Password: password123
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );

            // Create test user (only if doesn't exist)
            User::updateOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password123'), // Password: password123
                    'role' => 'user',
                    'is_active' => true,
                ]
            );
        }
    }
}
