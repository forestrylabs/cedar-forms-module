<?php

namespace Modules\Forms;

use App\Modules\ModuleServiceProvider;
use App\Services\Blocks\BlockRegistry;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Modules\Forms\Blocks\FormBlock;
use Modules\Forms\Console\Commands\FormsPruneSubmissionsCommand;
use Modules\Forms\Database\Seeders\FormsSeeder;
use Modules\Forms\Database\Seeders\FormsStarterSeeder;
use Modules\Forms\Filament\Resources\FormResource;
use Modules\Forms\Livewire\FormRenderer;
use Modules\Forms\Models\FormSubmission;

class FormsServiceProvider extends ModuleServiceProvider
{
    public static string $name = 'forms';

    public static array $dependencies = [];

    public static function filamentResources(): array
    {
        return [FormResource::class];
    }

    public static function seeders(): array
    {
        return [FormsSeeder::class];
    }

    public static function starterSeeders(): array
    {
        return [FormsStarterSeeder::class];
    }

    public static function dashboardStats(): array
    {
        return [
            Stat::make('Unread Submissions', (string) FormSubmission::query()->whereNull('read_at')->count())
                ->icon(Heroicon::OutlinedInbox)
                ->url(FormResource::getUrl('index')),
        ];
    }

    public static function dashboardQuickLinks(): array
    {
        return [
            ['label' => 'New Form', 'url' => FormResource::getUrl('create'), 'icon' => 'heroicon-o-clipboard-document-list'],
        ];
    }

    protected function bootModule(): void
    {
        Livewire::component('form-renderer', FormRenderer::class);

        // Lowest-priority fallback so `modules.forms.*` view lookups hit the
        // theme override first, then this bundled default (docs/02-architecture.md).
        View::addLocation(static::path().'/resources/views');

        app(BlockRegistry::class)->register(FormBlock::class);

        $this->commands([FormsPruneSubmissionsCommand::class]);
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('forms:prune-submissions')
            ->weekly()
            ->onOneServer()
            ->withoutOverlapping();
    }
}
