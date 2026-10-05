<?php

namespace Modules\Forms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Forms\Database\Factories\FormFactory;

#[Fillable([
    'name', 'slug', 'success_message', 'recipients', 'fields', 'settings', 'captcha_enabled', 'store_submissions',
])]
class Form extends Model
{
    /** @use HasFactory<FormFactory> */
    use HasFactory;

    /** Allowed values for the constrained appearance settings (first is the default). */
    public const LAYOUTS = ['stacked' => 'Stacked', 'inline' => 'Inline (fields and button on one row)'];

    public const WIDTHS = ['contained' => 'Contained', 'full' => 'Full width'];

    public const STYLES = ['plain' => 'Plain', 'card' => 'Card'];

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'fields' => 'array',
            'settings' => 'array',
            'captcha_enabled' => 'boolean',
            'store_submissions' => 'boolean',
        ];
    }

    protected static function newFactory(): FormFactory
    {
        return FormFactory::new();
    }

    /**
     * An appearance setting (layout, width, style, button_label), falling back
     * to the default when unset or no longer one of the allowed values.
     */
    public function setting(string $key): string
    {
        $value = $this->settings[$key] ?? null;

        if ($key === 'button_label') {
            return filled($value) ? (string) $value : 'Submit';
        }

        $allowed = match ($key) {
            'layout' => self::LAYOUTS,
            'width' => self::WIDTHS,
            'style' => self::STYLES,
        };

        return array_key_exists((string) $value, $allowed) ? $value : array_key_first($allowed);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function unreadCount(): int
    {
        return $this->submissions()->whereNull('read_at')->count();
    }

    /** The first email-type field's name, used to set the notification reply-to. */
    public function emailFieldName(): ?string
    {
        foreach ($this->fields as $field) {
            if (($field['type'] ?? null) === 'email') {
                return $field['name'];
            }
        }

        return null;
    }
}
