<?php

use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Modules\Forms\Livewire\FormRenderer;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

beforeEach(function () {
    enableModule('forms');
    RateLimiter::clear('forms:127.0.0.1');
});

it('rejects a submission with the honeypot field filled', function () {
    $form = Form::factory()->create();

    test()->travelTo(now()->addSeconds(3));

    Livewire::test(FormRenderer::class, ['form' => $form])
        ->set('values.name', 'Bot')
        ->set('values.email', 'bot@example.test')
        ->set('values.message', 'Buy now')
        ->set('website', 'https://spam.example')
        ->call('submit')
        ->assertHasErrors('form');

    expect(FormSubmission::count())->toBe(0);
});

it('rejects a submission made within 2 seconds of render', function () {
    $form = Form::factory()->create();

    Livewire::test(FormRenderer::class, ['form' => $form])
        ->set('values.name', 'Fast Bot')
        ->set('values.email', 'fast@example.test')
        ->set('values.message', 'Instant')
        ->call('submit')
        ->assertHasErrors('form');

    expect(FormSubmission::count())->toBe(0);
});

it('enforces a rate limit of 5 submissions per minute per IP', function () {
    $form = Form::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $component = Livewire::test(FormRenderer::class, ['form' => $form])
            ->set('values.name', "Person {$i}")
            ->set('values.email', "person{$i}@example.test")
            ->set('values.message', 'Hi');

        test()->travelTo(now()->addSeconds(3));

        $component->call('submit')->assertHasNoErrors();
    }

    expect(FormSubmission::count())->toBe(5);

    $component = Livewire::test(FormRenderer::class, ['form' => $form])
        ->set('values.name', 'One too many')
        ->set('values.email', 'toomany@example.test')
        ->set('values.message', 'Hi');

    test()->travelTo(now()->addSeconds(3));

    $component->call('submit')->assertHasErrors('form');

    expect(FormSubmission::count())->toBe(5);
});
