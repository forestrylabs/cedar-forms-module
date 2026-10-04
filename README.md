# CedarCMS Forms Module

Form builder, submissions, and spam defenses for [CedarCMS Core](https://github.com/forestrylabs/cedar-cms).

A CedarCMS module (WordPress-plugin style): install it into a CedarCMS site and enable it in **Settings → Modules**. Installed brings the migrations; enabled loads the Filament resource, the form-renderer Livewire component, the `FormBlock` page-builder block, routes, and the weekly submission-prune schedule.

## Install

```bash
composer require forestry/cedar-forms-module:^0.1@beta
```

The service provider is auto-discovered. Then enable the module:

```bash
php artisan module:enable forms
```

## Requires

- `forestry/cedar-cms-core` (the host CMS)
- PHP 8.5+

Migrations run while the package is installed (even when the module is disabled), so toggling the module off never drops the `forms` / `form_submissions` tables.

## Starter content

On a new site, the installer's starter tier adds a **Contact** form and page (linked from the header menu). Recipients default to the site admin's email. Edit them under **Forms**.

## Development

The module compiles against core, so its tests run inside a core checkout (see `.github/workflows/ci.yml` and core's `docs/module-development.md`).

## License

MIT
