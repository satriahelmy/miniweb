<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:make-admin 
                            {email? : Email of the admin user} 
                            {--name= : Name of the admin user}';

    protected $description = 'Create or promote a user account to admin role in a secure way';
    public function handle()
    {
        $this->info('=== Create / Promote Admin User ===');

        $email = $this->argument('email') 
            ?? $this->ask('Enter admin email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format.');
            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->warn("User with email {$email} already exists.");

            if (! $this->confirm('Do you want to promote this user to admin?', true)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            $user->role = 'admin';
            $user->is_active = true;
            $user->save();

            $this->info("User {$email} has been promoted to admin.");
            return Command::SUCCESS;
        }

        $name = $this->option('name') ?: $this->ask('Enter admin name');
        $password = $this->secret('Enter admin password (will not be shown)');
        $passwordConfirm = $this->secret('Confirm admin password');

        if ($password !== $passwordConfirm) {
            $this->error('Password confirmation does not match.');
            return Command::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->warn('Password is less than 8 characters. Strongly recommended to use a stronger password.');
            if (! $this->confirm('Do you still want to continue?', false)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->info("Admin user created successfully.");
        $this->line("ID: {$user->id}");
        $this->line("Email: {$user->email}");

        return Command::SUCCESS;
    }
}
