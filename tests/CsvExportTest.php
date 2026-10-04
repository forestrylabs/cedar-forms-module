<?php

use Modules\Forms\Filament\Resources\FormResource\RelationManagers\SubmissionsRelationManager;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

beforeEach(function () {
    enableModule('forms');
});

it('exports submissions to csv with columns matching the form schema', function () {
    $form = Form::factory()->create([
        'fields' => [
            ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
            ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
        ],
    ]);

    FormSubmission::factory()->create([
        'form_id' => $form->id,
        'data' => ['name' => 'Jane Doe', 'email' => 'jane@example.test'],
    ]);

    $response = SubmissionsRelationManager::csvResponse($form);

    ob_start();
    $response->sendContent();
    $csv = ob_get_clean();

    $rows = array_map('str_getcsv', explode("\n", trim($csv)));

    expect($rows[0])->toBe(['Name', 'Email', 'Submitted at', 'IP address'])
        ->and($rows[1])->toMatchArray(['Jane Doe', 'jane@example.test']);
});
