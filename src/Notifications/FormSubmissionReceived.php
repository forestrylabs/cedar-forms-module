<?php

namespace Modules\Forms\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Forms\Models\Form;

class FormSubmissionReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param array<string, mixed> $data */
    public function __construct(public Form $form, public array $data, public ?string $replyTo) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("New submission: {$this->form->name}")
            ->greeting('New form submission');

        foreach ($this->form->fields as $field) {
            $value = $this->data[$field['name']] ?? null;

            if ($field['type'] === 'checkbox') {
                $value = $value ? 'Yes' : 'No';
            }

            $mail->line(($field['label'] ?? $field['name']).': '.(blank($value) ? '—' : $value));
        }

        if ($this->replyTo !== null) {
            $mail->replyTo($this->replyTo);
        }

        return $mail;
    }
}
