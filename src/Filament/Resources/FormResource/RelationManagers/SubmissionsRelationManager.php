<?php

namespace Modules\Forms\Filament\Resources\FormResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Submissions';

    public function table(Table $table): Table
    {
        /** @var Form $form */
        $form = $this->getOwnerRecord();

        $columns = [
            IconColumn::make('read_at')->label('Read')->boolean()->getStateUsing(fn (FormSubmission $record) => $record->read_at !== null),
        ];

        foreach (array_slice($form->fields, 0, 4) as $field) {
            $columns[] = TextColumn::make("data.{$field['name']}")
                ->label($field['label'])
                ->limit(40);
        }

        $columns[] = TextColumn::make('created_at')->label('Submitted')->dateTime()->sortable();

        return $table
            ->columns($columns)
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Submission')
                    ->modalContent(fn (FormSubmission $record) => view('modules.forms.submission-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('markRead')
                    ->label('Mark read')
                    ->icon('heroicon-o-check')
                    ->visible(fn (FormSubmission $record) => $record->read_at === null)
                    ->action(fn (FormSubmission $record) => $record->markRead()),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => static::csvResponse($form))
                    ->deselectRecordsAfterCompletion(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function canCreate(): bool
    {
        return false;
    }

    public static function csvResponse(Form $form): StreamedResponse
    {
        $columns = array_map(fn (array $field) => $field['name'], $form->fields);
        $labels = array_map(fn (array $field) => $field['label'], $form->fields);

        $filename = Str::slug($form->name).'-submissions.csv';

        return Response::streamDownload(function () use ($form, $columns, $labels) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [...$labels, 'Submitted at', 'IP address']);

            $form->submissions()->orderBy('created_at')->each(function (FormSubmission $submission) use ($handle, $columns) {
                fputcsv($handle, [
                    ...array_map(fn (string $column) => $submission->data[$column] ?? '', $columns),
                    $submission->created_at->toDateTimeString(),
                    $submission->ip_address,
                ]);
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
