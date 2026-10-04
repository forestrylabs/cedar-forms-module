<?php

use Illuminate\Support\Facades\Artisan;
use Modules\Forms\Console\Commands\FormsPruneSubmissionsCommand;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

beforeEach(function () {
    enableModule('forms');

    // Mirrors CommentsPruneCommand's test setup: the module's provider
    // registers this command via the deferred Artisan::starting event, which
    // already fired before this test's mid-test module enable.
    Artisan::registerCommand(app(FormsPruneSubmissionsCommand::class));
});

it('prunes submissions older than the retention window but keeps recent ones', function () {
    $form = Form::factory()->create();

    $old = FormSubmission::factory()->create(['form_id' => $form->id, 'created_at' => now()->subDays(400)]);
    $recent = FormSubmission::factory()->create(['form_id' => $form->id, 'created_at' => now()->subDays(10)]);

    $this->artisan('forms:prune-submissions')->assertSuccessful();

    expect(FormSubmission::find($old->id))->toBeNull()
        ->and(FormSubmission::find($recent->id))->not->toBeNull();
});

it('respects a custom --days option', function () {
    $form = Form::factory()->create();

    $submission = FormSubmission::factory()->create(['form_id' => $form->id, 'created_at' => now()->subDays(40)]);

    $this->artisan('forms:prune-submissions', ['--days' => 30])->assertSuccessful();

    expect(FormSubmission::find($submission->id))->toBeNull();
});
