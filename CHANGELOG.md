# Changelog

## 0.1.0-beta.1

First public pre-release.

- Form builder (Filament resource) with text, email, textarea, select, radio and checkbox fields.
- Livewire form renderer and `form` page-builder block.
- Spam defenses: honeypot, minimum submit time, per-IP rate limit, optional captcha.
- Stored submissions with unread tracking, CSV export and weekly pruning (`forms:prune-submissions`).
- Email notification to configured recipients, with reply-to from the email field.
- Starter seeder: a Contact form and page, linked from the header menu.
