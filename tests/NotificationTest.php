<?php

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Modules\Forms\Livewire\FormRenderer;
use Modules\Forms\Models\Form;
use Modules\Forms\Notifications\FormSubmissionReceived;

beforeEach(function () {
    enableModule('forms');
});

it('queues a notification to every recipient with reply-to set from the email field', function () {
    Notification::fake();

    $form = Form::factory()->create([
        'recipients' => ['sales@example.test', 'support@example.test'],
    ]);

    $component = Livewire::test(FormRenderer::class, ['form' => $form])
        ->set('values.name', 'Jane Doe')
        ->set('values.email', 'jane@example.test')
        ->set('values.message', 'Hello there');

    test()->travelTo(now()->addSeconds(3));

    $component->call('submit')->assertHasNoErrors();

    Notification::assertSentOnDemand(
        FormSubmissionReceived::class,
        function (FormSubmissionReceived $notification, array $channels, $notifiable) {
            return $notifiable->routes['mail'] === ['sales@example.test', 'support@example.test']
                && $notification->replyTo === 'jane@example.test';
        }
    );
});

it('is a queued notification', function () {
    expect(is_subclass_of(FormSubmissionReceived::class, ShouldQueue::class))->toBeTrue();
});
