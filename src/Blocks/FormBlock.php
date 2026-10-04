<?php

namespace Modules\Forms\Blocks;

use App\Services\Blocks\Block;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Modules\Forms\Models\Form;

class FormBlock extends Block
{
    public static function name(): string
    {
        return 'form';
    }

    public static function label(): string
    {
        return 'Form';
    }

    public static function icon(): Heroicon
    {
        return Heroicon::OutlinedClipboardDocumentList;
    }

    public static function schema(): array
    {
        return [
            Select::make('form_id')
                ->label('Form')
                ->options(fn () => Form::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),
        ];
    }
}
