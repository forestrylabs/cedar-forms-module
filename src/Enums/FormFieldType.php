<?php

namespace Modules\Forms\Enums;

enum FormFieldType: string
{
    case Text = 'text';
    case Email = 'email';
    case Textarea = 'textarea';
    case Select = 'select';
    case Checkbox = 'checkbox';
    case Radio = 'radio';
    case Phone = 'phone';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Email => 'Email',
            self::Textarea => 'Textarea',
            self::Select => 'Select',
            self::Checkbox => 'Checkbox',
            self::Radio => 'Radio',
            self::Phone => 'Phone',
            self::Hidden => 'Hidden',
        };
    }

    /** Field types that need an `options` list of choices. */
    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::Radio], true);
    }
}
