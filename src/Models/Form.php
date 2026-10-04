<?php

namespace Modules\Forms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Forms\Database\Factories\FormFactory;

#[Fillable([
    'name', 'slug', 'success_message', 'recipients', 'fields', 'captcha_enabled', 'store_submissions',
])]
class Form extends Model
{
    /** @use HasFactory<FormFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'recipients' => 'array',
            'fields' => 'array',
            'captcha_enabled' => 'boolean',
            'store_submissions' => 'boolean',
        ];
    }

    protected static function newFactory(): FormFactory
    {
        return FormFactory::new();
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
