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
        // Only create a non-admin test user in development/testing environments
        // NEVER create default admin users in any environment!
        if (app()->environment(['local', 'testing', 'development'])) {
            User::updateOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password123'),
                    'role' => 'user',
                    'is_active' => true,
                ]
            );
        }
    }
}
