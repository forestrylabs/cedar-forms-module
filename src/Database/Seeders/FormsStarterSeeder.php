<?php

namespace Modules\Forms\Database\Seeders;

use App\Models\User;
use Modules\Forms\Models\Form;

/**
 * Starter content for a new site: the same Contact form, page and header menu
 * link as the demo seeder, but notifications go to the site's super admin
 * rather than a placeholder address. Never overwrites a form the owner edited.
 */
class FormsStarterSeeder extends FormsSeeder
{
    protected function recipients(): array
    {
        $email = User::role('super_admin')->orderBy('id')->value('email');

        return $email ? [$email] : [];
    }

    public function run(): void
    {
        if (Form::query()->where('slug', 'contact')->exists()) {
            return;
        }

        $this->contact();
    }
}
