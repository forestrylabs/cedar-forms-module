<?php

namespace Modules\Forms\Filament\Resources\FormResource\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Modules\Forms\Enums\FormFieldType;
use Modules\Forms\Models\Form;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set, ?Form $record) => $record === null && $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Used to embed this form on a page via the Form block.')
                        ->unique(table: 'forms', column: 'slug', ignoreRecord: true),
                    TextInput::make('success_message')
                        ->maxLength(255)
                        ->default("Thanks — we'll be in touch soon.")
                        ->helperText('Shown to visitors after they submit the form.'),
                    Toggle::make('captcha_enabled')
                        ->label('Require captcha')
                        ->helperText('Adds spam protection using the site\'s configured captcha provider.'),
                    Toggle::make('store_submissions')
                        ->label('Store submissions')
                        ->default(true)
                        ->helperText('Keep a copy of each submission in the admin, in addition to emailing recipients.'),
                ])
                ->columns(2),
            Section::make('Recipients')
                ->schema([
                    Repeater::make('recipients')
                        ->simple(TextInput::make('email')->email()->required())
                        ->required()
                        ->minItems(1)
                        ->addActionLabel('Add recipient'),
                ]),
            Section::make('Fields')
                ->schema([
                    Repeater::make('fields')
                        ->schema([
                            Select::make('type')
                                ->options(collect(FormFieldType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                                ->required()
                                ->live(),
                            TextInput::make('label')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set, callable $get) => filled($get('name')) || $set('name', Str::snake($state))),
                            TextInput::make('name')
                                ->required()
                                ->helperText('Key stored in the submission data.'),
                            TextInput::make('placeholder'),
                            Toggle::make('required'),
                            TextInput::make('max_length')->numeric(),
                            Repeater::make('options')
                                ->simple(TextInput::make('option')->required())
                                ->visible(fn (Get $get) => in_array($get('type'), ['select', 'radio'], true))
                                ->addActionLabel('Add option'),
                        ])
                        ->columns(2)
                        ->required()
                        ->minItems(1)
                        ->reorderable()
                        ->addActionLabel('Add field'),
                ]),
        ]);
    }
}
