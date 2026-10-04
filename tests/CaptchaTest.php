<?php

use App\Facades\Captcha;
use Livewire\Livewire;
use Modules\Forms\Livewire\FormRenderer;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

beforeEach(function () {
    enableModule('forms');
});

it('submits when captcha is faked to pass', function () {
    Captcha::fake(true);

    $form = Form::factory()->withCaptcha()->create();

    $component = Livewire::test(FormRenderer::class, ['form' => $form])
        ->set('values.name', 'Jane')
        ->set('values.email', 'jane@example.test')
        ->set('values.message', 'Hi')
        ->set('captcha_token', 'any-token');

    test()->travelTo(now()->addSeconds(3));

    $component->call('submit')->assertHasNoErrors();

    expect(FormSubmission::count())->toBe(1);
});

it('rejects submission when captcha is faked to fail', function () {
    Captcha::fake(false);

    $form = Form::factory()->withCaptcha()->create();

    $component = Livewire::test(FormRenderer::class, ['form' => $form])
        ->set('values.name', 'Jane')
        ->set('values.email', 'jane@example.test')
        ->set('values.message', 'Hi')
        ->set('captcha_token', 'any-token');

    test()->travelTo(now()->addSeconds(3));

    $component->call('submit')->assertHasErrors('form');

    expect(FormSubmission::count())->toBe(0);
});

it('skips captcha silently when the form does not require it, even with provider none', function () {
    $form = Form::factory()->create(['captcha_enabled' => false]);

    $component = Livewire::test(FormRenderer::class, ['form' => $form])
        ->set('values.name', 'Jane')
        ->set('values.email', 'jane@example.test')
        ->set('values.message', 'Hi');

    test()->travelTo(now()->addSeconds(3));

    $component->call('submit')->assertHasNoErrors();

    expect(FormSubmission::count())->toBe(1);
});
