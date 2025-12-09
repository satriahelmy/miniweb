<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class CleanupAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:cleanup 
                            {--days=90 : Number of days to keep success logs}
                            {--critical-days=365 : Number of days to keep critical logs (failed/unauthorized)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old audit logs based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $criticalDays = (int) $this->option('critical-days');
        $dryRun = $this->option('dry-run');

        $this->info("Audit Log Cleanup");
        $this->info("==================");
        $this->line("");

        // Count logs to be deleted
        $successLogs = AuditLog::where('status', 'success')
            ->where('created_at', '<', now()->subDays($days))
            ->count();

        $criticalLogs = AuditLog::whereIn('status', ['failed', 'blocked', 'unauthorized'])
            ->where('created_at', '<', now()->subDays($criticalDays))
            ->count();

        $this->info("Logs to be deleted:");
        $this->line("  - Success logs older than {$days} days: {$successLogs}");
        $this->line("  - Critical logs (failed/blocked/unauthorized) older than {$criticalDays} days: {$criticalLogs}");
        $this->line("  - Total: " . ($successLogs + $criticalLogs));
        $this->line("");

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No logs were deleted");
            return Command::SUCCESS;
        }

        if (($successLogs + $criticalLogs) === 0) {
            $this->info("No logs to delete.");
            return Command::SUCCESS;
        }

        if (!$this->confirm('Do you want to proceed with deletion?', true)) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Delete success logs
        $deletedSuccess = AuditLog::where('status', 'success')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        // Delete critical logs (after longer retention)
        $deletedCritical = AuditLog::whereIn('status', ['failed', 'blocked', 'unauthorized'])
            ->where('created_at', '<', now()->subDays($criticalDays))
            ->delete();

        $this->info("Successfully deleted:");
        $this->line("  - Success logs: {$deletedSuccess}");
        $this->line("  - Critical logs: {$deletedCritical}");
        $this->line("  - Total deleted: " . ($deletedSuccess + $deletedCritical));

        return Command::SUCCESS;
    }
}
