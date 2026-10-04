<?php

namespace Modules\Forms\Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Forms\Models\Form;

/**
 * Demo content for the forms module: a Contact form, a Contact page that
 * renders it, and a header menu link. Run by core's DatabaseSeeder for every
 * installed module via FormsServiceProvider::seeders(), after core content.
 */
class FormsSeeder extends Seeder
{
    /** Notification recipients for the seeded Contact form. */
    protected function recipients(): array
    {
        return ['hello@example.test'];
    }

    public function run(): void
    {
        $form = Form::updateOrCreate(['slug' => 'contact'], [
            'name' => 'Contact',
            'success_message' => "Thanks for reaching out — we'll be in touch soon.",
            'recipients' => $this->recipients(),
            'fields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => 255],
                ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => 255],
                ['type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'placeholder' => null, 'required' => true, 'options' => [], 'max_length' => null],
            ],
            'captcha_enabled' => false,
            'store_submissions' => true,
        ]);

        $contact = Page::updateOrCreate(['slug' => 'contact', 'parent_id' => null], [
            'title' => 'Contact',
            'template' => 'default',
            'excerpt' => 'Get in touch.',
            'status' => PublishStatus::Published,
            'published_at' => now(),
            'blocks' => $this->blocks([
                ['type' => 'hero', 'data' => [
                    'heading' => 'Contact us',
                    'subheading' => "We'd love to hear from you.",
                    'alignment' => 'left',
                    'background_media_id' => null,
                ]],
                ['type' => 'form', 'data' => ['form_id' => $form->id]],
            ]),
        ]);

        // Add a header menu link to the contact page (idempotent).
        Menu::updateOrCreate(['location' => 'header'], ['name' => 'Header'])
            ->items()->updateOrCreate(
                ['linkable_type' => $contact->getMorphClass(), 'linkable_id' => $contact->id],
                ['label' => 'Contact', 'sort_order' => 3],
            );
    }

    /**
     * Wrap block items in the keyed shape the page builder stores
     * (see PagesSeeder::block() in core): [uuid => ['type' => ..., 'data' => ...]].
     *
     * @param  list<array{type: string, data: array}>  $items
     */
    protected function blocks(array $items): array
    {
        $blocks = [];

        foreach ($items as $item) {
            $blocks[(string) Str::uuid()] = ['type' => $item['type'], 'data' => $item['data']];
        }

        return $blocks;
    }
}
