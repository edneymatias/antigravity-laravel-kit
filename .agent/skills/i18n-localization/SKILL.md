---
name: i18n-localization
description: Internationalization and localization patterns. Detecting hardcoded strings, managing translations, locale files, RTL support.
allowed-tools: Read, Glob, Grep
---

# i18n & Localization (Laravel)

> Best practices for Laravel localization.

---

## 1. Laravel Localization Basics

Laravel provides two ways to manage translations:

### Short Keys (`lang/en/messages.php`)
Good for large apps with structured text.
```php
// lang/en/messages.php
return [
    'welcome' => 'Welcome to our application',
];

// Usage
echo __('messages.welcome');
```

### JSON Strings (`lang/en.json`)
Good for prototyping or smaller apps.
```json
// lang/en.json
{
    "I love programming.": "Eu amo programar."
}

// Usage
echo __('I love programming.');
```

---

## 2. Implementation Patterns

### Blade Templates
```blade
{{-- Standard --}}
<h1>{{ __('messages.welcome') }}</h1>

{{-- Direct Directive --}}
@lang('auth.failed')

{{-- With Replacements --}}
<p>{{ __('Welcome, :name', ['name' => $user->name]) }}</p>

{{-- Pluralization --}}
<p>{{ trans_choice('messages.apples', 10) }}</p>
```

### Controllers / PHP
```php
// Helper
$message = __('messages.saved');

// Facade
use Illuminate\Support\Facades\Lang;
$message = Lang::get('messages.saved');
```

---

## 3. Recommended Packages

For robust localization management, don't reinvent the wheel.

### `laravel-lang/common`
Official translations for validation, auth, and pagination messages in 75+ languages.

```bash
composer require laravel-lang/common --dev
php artisan lang:update
```

### `laravel-lang/publisher`
Easily publish and manage language files.

---

## 4. Helper Script

The kit includes a simple script to detect hardcoded strings in Blade templates.

```bash
php .agent/skills/i18n-localization/scripts/i18n_checker.php resources/views
```

This will scan your views and report strings that look like text but aren't wrapped in translation helpers.

---

## 5. Security Checklist

- [ ] Use `__()` for output to ensure it goes through the translation layer (and escaping).
- [ ] Don't trust user input in translation keys.
- [ ] Be careful with `{!! !!}` when displaying translations containing HTML.

---
