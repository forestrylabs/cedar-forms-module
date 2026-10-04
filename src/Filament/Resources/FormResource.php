<?php

namespace Modules\Forms\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Modules\Forms\Filament\Resources\FormResource\Pages\CreateForm;
use Modules\Forms\Filament\Resources\FormResource\Pages\EditForm;
use Modules\Forms\Filament\Resources\FormResource\Pages\ListForms;
use Modules\Forms\Filament\Resources\FormResource\RelationManagers\SubmissionsRelationManager;
use Modules\Forms\Filament\Resources\FormResource\Schemas\FormForm;
use Modules\Forms\Filament\Resources\FormResource\Tables\FormsTable;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;
use UnitEnum;

class FormResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Modules';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug'];
    }

    /** @return array<string, string> */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Submissions' => (string) $record->submissions_count,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->withCount('submissions');
    }

    public static function form(Schema $schema): Schema
    {
        return FormForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FormsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [SubmissionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListForms::route('/'),
            'create' => CreateForm::route('/create'),
            'edit' => EditForm::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Cache::remember(
            'filament.nav-badge.forms.unread-submissions',
            now()->addMinute(),
            fn () => FormSubmission::query()->whereNull('read_at')->count(),
        );
    }
}
