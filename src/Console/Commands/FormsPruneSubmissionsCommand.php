<?php

namespace Modules\Forms\Console\Commands;

use Illuminate\Console\Command;
use Modules\Forms\Models\FormSubmission;

class FormsPruneSubmissionsCommand extends Command
{
    protected $signature = 'forms:prune-submissions {--days=365 : Delete submissions older than this many days}';

    protected $description = 'Delete form submissions older than the retention window';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $count = FormSubmission::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->components->info("Pruned {$count} form submission(s).");

        return self::SUCCESS;
    }
}
