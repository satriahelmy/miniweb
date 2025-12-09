<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RemoveDefaultUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:remove-default {--force : Force removal without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove default test users (admin@example.com and test@example.com) for security';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $defaultEmails = ['admin@example.com', 'test@example.com'];
        $usersToDelete = User::whereIn('email', $defaultEmails)->get();

        if ($usersToDelete->isEmpty()) {
            $this->info('No default users found.');
            return Command::SUCCESS;
        }

        $this->warn('The following default users will be deleted:');
        foreach ($usersToDelete as $user) {
            $this->line("  - {$user->email} ({$user->name})");
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to proceed?', true)) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $deletedCount = 0;
        foreach ($usersToDelete as $user) {
            $user->delete();
            $deletedCount++;
        }

        $this->info("Successfully deleted {$deletedCount} default user(s).");
        $this->warn('Security: Default users have been removed. Make sure to create proper admin accounts if needed.');

        return Command::SUCCESS;
    }
}
