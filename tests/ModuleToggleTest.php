<?php

use App\Models\Page;
use App\Modules\ModuleManager;
use Modules\Forms\Filament\Resources\FormResource;
use Modules\Forms\Models\Form;

it('does not register the block or the admin resource when the module is disabled', function () {
    $form = Form::factory()->create();

    $page = Page::factory()->create([
        'blocks' => ['x' => ['type' => 'form', 'data' => ['form_id' => $form->id]]],
    ]);

    $this->get('/'.$page->path())->assertOk()->assertDontSee('wire:submit="submit"', false);

    asEditor();
    $this->get('/admin/forms')->assertNotFound();

    expect(app(ModuleManager::class)->filamentResources())->not->toContain(FormResource::class);
});

it('renders the form block and registers the admin resource once enabled', function () {
    enableModule('forms');

    $form = Form::factory()->create();

    $page = Page::factory()->create([
        'blocks' => ['x' => ['type' => 'form', 'data' => ['form_id' => $form->id]]],
    ]);

    $this->get('/'.$page->path())->assertOk()->assertSee('wire:submit="submit"', false);

    expect(app(ModuleManager::class)->filamentResources())->toContain(FormResource::class);
});
