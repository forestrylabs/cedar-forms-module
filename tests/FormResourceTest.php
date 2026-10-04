<?php

use Modules\Forms\Filament\Resources\FormResource;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

beforeEach(function () {
    enableModule('forms');
});

it('reports an unread submission count on the navigation badge', function () {
    $form = Form::factory()->create();

    FormSubmission::factory()->count(2)->create(['form_id' => $form->id]);
    FormSubmission::factory()->read()->create(['form_id' => $form->id]);

    expect(FormResource::getNavigationBadge())->toBe('2');
});

it('marks a submission as read', function () {
    $form = Form::factory()->create();
    $submission = FormSubmission::factory()->create(['form_id' => $form->id]);

    $submission->markRead();

    expect($submission->fresh()->read_at)->not->toBeNull();
});
