<?php

use App\Models\Page;
use Database\Seeders\StarterSeeder;
use Modules\Forms\Database\Seeders\FormsStarterSeeder;
use Modules\Forms\Models\Form;

beforeEach(fn () => enableModule('forms'));

it('seeds a contact form that notifies the super admin', function () {
    $admin = asSuperAdmin();

    $this->seed(FormsStarterSeeder::class);

    expect(Form::where('slug', 'contact')->first()->recipients)->toBe([$admin->email])
        ->and(Page::where('slug', 'contact')->exists())->toBeTrue();
});

it('does not overwrite a contact form the owner edited', function () {
    asSuperAdmin();
    $this->seed(FormsStarterSeeder::class);
    Form::where('slug', 'contact')->update(['recipients' => ['owner@example.com']]);

    $this->seed(FormsStarterSeeder::class);

    expect(Form::where('slug', 'contact')->first()->recipients)->toBe(['owner@example.com']);
});

it('is run by the core starter seeder', function () {
    asSuperAdmin();

    $this->seed(StarterSeeder::class);

    expect(Form::where('slug', 'contact')->exists())->toBeTrue();
});
