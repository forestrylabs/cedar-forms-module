<?php

namespace Modules\Forms\Livewire;

use App\Facades\Captcha;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;
use Modules\Forms\Notifications\FormSubmissionReceived;

class FormRenderer extends Component
{
    public Form $form;

    /** @var array<string, mixed> */
    public array $values = [];

    /** Honeypot: real visitors never fill this (hidden via CSS). */
    public string $website = '';

    public string $captcha_token = '';

    public bool $submitted = false;

    /** ISO timestamp captured at mount, used for the min-submit-time check. */
    public string $renderedAt;

    public function mount(Form $form): void
    {
        $this->form = $form;
        $this->renderedAt = now()->toISOString();

        foreach ($form->fields as $field) {
            $this->values[$field['name']] = $field['type'] === 'checkbox' ? false : '';
        }
    }

    public function rules(): array
    {
        $rules = [];

        foreach ($this->form->fields as $field) {
            $rules["values.{$field['name']}"] = $this->fieldRules($field);
        }

        return $rules;
    }

    protected function fieldRules(array $field): array
    {
        $rules = [! empty($field['required']) ? 'required' : 'nullable'];

        $rules[] = match ($field['type']) {
            'email' => 'email',
            'checkbox' => 'boolean',
            default => 'string',
        };

        if (in_array($field['type'], ['select', 'radio'], true) && ! empty($field['options'])) {
            $rules[] = Rule::in(array_values($field['options']));
        }

        if (! empty($field['max_length'])) {
            $rules[] = 'max:'.$field['max_length'];
        }

        return $rules;
    }

    public function submit(): void
    {
        if ($this->website !== '') {
            $this->addError('form', 'Something went wrong — please try again.');

            return;
        }

        if (abs(now()->diffInSeconds($this->renderedAt)) < 2) {
            $this->addError('form', 'Please try again.');

            return;
        }

        $key = 'forms:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('form', 'Too many submissions — please wait a minute before trying again.');

            return;
        }

        RateLimiter::hit($key, 60);

        $this->validate();

        if ($this->form->captcha_enabled && ! Captcha::isNoop() && ! Captcha::verify($this->captcha_token, request()->ip())) {
            $this->addError('form', 'Captcha verification failed.');

            return;
        }

        if ($this->form->store_submissions) {
            FormSubmission::create([
                'form_id' => $this->form->id,
                'data' => $this->values,
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
            ]);
        }

        if (! empty($this->form->recipients)) {
            $replyTo = $this->emailFieldValue();

            Notification::route('mail', $this->form->recipients)
                ->notify(new FormSubmissionReceived($this->form, $this->values, $replyTo));
        }

        $this->submitted = true;
        $this->dispatch('form-submit', slug: $this->form->slug);
        $this->reset(['values', 'website', 'captcha_token']);
    }

    protected function emailFieldValue(): ?string
    {
        $name = $this->form->emailFieldName();

        return $name !== null ? ($this->values[$name] ?? null) : null;
    }

    public function render()
    {
        return view('modules.forms.form-renderer');
    }
}
