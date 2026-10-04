<?php

namespace Modules\Forms\Filament\Resources\FormResource\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Forms\Models\Form;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('slug'),
                IconColumn::make('captcha_enabled')->label('Captcha')->boolean(),
                IconColumn::make('store_submissions')->label('Storing')->boolean(),
                TextColumn::make('submissions_count')
                    ->label('Submissions')
                    ->counts('submissions'),
                TextColumn::make('unread')
                    ->label('Unread')
                    ->getStateUsing(fn (Form $record) => $record->unreadCount()),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
