<?php

use Livewire\Livewire;
use Modules\Forms\Livewire\FormRenderer;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

beforeEach(function () {
    enableModule('forms');
});

function submitAfterMinTime($component)
{
    test()->travelTo(now()->addSeconds(3));

    return $component->call('submit');
}

it('renders, validates, and round-trips every field type into the submission json', function () {
    $form = Form::factory()->create([
        'fields' => [
            ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
            ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
            ['type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'placeholder' => null, 'required' => false, 'options' => [], 'max_length' => null],
            ['type' => 'select', 'name' => 'topic', 'label' => 'Topic', 'placeholder' => null, 'required' => true, 'options' => ['Sales', 'Support'], 'max_length' => null],
            ['type' => 'checkbox', 'name' => 'subscribe', 'label' => 'Subscribe', 'placeholder' => null, 'required' => false, 'options' => [], 'max_length' => null],
            ['type' => 'radio', 'name' => 'plan', 'label' => 'Plan', 'placeholder' => null, 'required' => true, 'options' => ['Basic', 'Pro'], 'max_length' => null],
            ['type' => 'phone', 'name' => 'phone', 'label' => 'Phone', 'placeholder' => null, 'required' => false, 'options' => [], 'max_length' => null],
            ['type' => 'hidden', 'name' => 'source', 'label' => 'Source', 'placeholder' => null, 'required' => false, 'options' => [], 'max_length' => null],
        ],
    ]);

    $component = Livewire::test(FormRenderer::class, ['form' => $form])
        ->set('values.name', 'Jane Doe')
        ->set('values.email', 'jane@example.test')
        ->set('values.message', 'Hello there')
        ->set('values.topic', 'Support')
        ->set('values.subscribe', true)
        ->set('values.plan', 'Pro')
        ->set('values.phone', '555-0100')
        ->set('values.source', 'homepage');

    submitAfterMinTime($component)->assertHasNoErrors();

    $submission = FormSubmission::first();

    expect($submission)->not->toBeNull()
        ->and($submission->data)->toMatchArray([
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'message' => 'Hello there',
            'topic' => 'Support',
            'subscribe' => true,
            'plan' => 'Pro',
            'phone' => '555-0100',
            'source' => 'homepage',
        ]);
});

it('rejects a required field left blank', function () {
    $form = Form::factory()->create([
        'fields' => [
            ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
        ],
    ]);

    $component = Livewire::test(FormRenderer::class, ['form' => $form])->set('values.name', '');

    submitAfterMinTime($component)->assertHasErrors('values.name');

    expect(FormSubmission::count())->toBe(0);
});

it('rejects an invalid email format', function () {
    $form = Form::factory()->create([
        'fields' => [
            ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
        ],
    ]);

    $component = Livewire::test(FormRenderer::class, ['form' => $form])->set('values.email', 'not-an-email');

    submitAfterMinTime($component)->assertHasErrors('values.email');

    expect(FormSubmission::count())->toBe(0);
});
