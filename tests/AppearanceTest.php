<?php

use App\Models\Page;
use Livewire\Livewire;
use Modules\Forms\Livewire\FormRenderer;
use Modules\Forms\Models\Form;

beforeEach(fn () => enableModule('forms'));

it('falls back to defaults when settings are missing or invalid', function () {
    $form = Form::factory()->create(['settings' => ['layout' => 'bogus', 'width' => null]]);

    expect($form->setting('layout'))->toBe('stacked')
        ->and($form->setting('width'))->toBe('contained')
        ->and($form->setting('style'))->toBe('plain')
        ->and($form->setting('button_label'))->toBe('Submit');
});

it('renders layout, style and button label hooks', function () {
    $form = Form::factory()->create(['settings' => ['layout' => 'inline', 'width' => 'full', 'style' => 'card', 'button_label' => 'Subscribe']]);

    Livewire::test(FormRenderer::class, ['form' => $form])
        ->assertSeeHtml('cedar-form--inline')
        ->assertSeeHtml('cedar-form--full')
        ->assertSeeHtml('cedar-form--card')
        ->assertSee('Subscribe');
});

it('lets the block override the form width but ignores unknown values', function () {
    $form = Form::factory()->create(['settings' => ['width' => 'contained']]);

    Livewire::test(FormRenderer::class, ['form' => $form, 'width' => 'full'])->assertSeeHtml('cedar-form--full');
    Livewire::test(FormRenderer::class, ['form' => $form, 'width' => 'inherit'])->assertSeeHtml('cedar-form--contained');
    Livewire::test(FormRenderer::class, ['form' => $form, 'width' => 'x"onload'])->assertSeeHtml('cedar-form--contained');
});

it('passes the block width through to the rendered page', function () {
    $form = Form::factory()->create();
    $page = Page::factory()->create([
        'blocks' => ['x' => ['type' => 'form', 'data' => ['form_id' => $form->id, 'width' => 'full']]],
    ]);

    $this->get('/'.$page->path())->assertOk()->assertSee('cedar-form--full', false);
});
