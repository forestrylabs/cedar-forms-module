<?php

namespace Modules\Forms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Forms\Models\Form;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        $name = 'Contact '.fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'success_message' => "Thanks — we'll be in touch soon.",
            'recipients' => [fake()->safeEmail()],
            'fields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
                ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
                ['type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
            ],
            'captcha_enabled' => false,
            'store_submissions' => true,
        ];
    }

    public function withCaptcha(): static
    {
        return $this->state(['captcha_enabled' => true]);
    }
}
