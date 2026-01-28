---
name: seo-fundamentals
description: SEO best practices for Laravel. Meta tags, OpenGraph, Sitemap generation, and Structured Data.
allowed-tools: Read, Glob, Grep
---

# SEO for Laravel

> Technical and content SEO practices optimized for the Laravel ecosystem.

---

## 1. Essential Packages

Don't manually manage meta tags. Use battle-tested packages.

### Spatie Laravel SEO (Recommended)
Automatically generates meta tags and structured data.

```bash
composer require spatie/laravel-tags spatie/laravel-seo
```

### Spatie Laravel Sitemap
Generates `sitemap.xml` dynamically.

```bash
composer require spatie/laravel-sitemap
```

---

## 2. Implementation Patterns

### Meta Tags in Blade
Using a shared layout (`resources/views/layouts/app.blade.php`):

```blade
<head>
    {{-- Basic Meta --}}
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('description', 'Default app description')">

    {{-- Open Graph / Social --}}
    <meta property="og:title" content="@yield('title')" />
    <meta property="og:description" content="@yield('description')" />
    <meta property="og:image" content="@yield('image', asset('images/og-default.jpg'))" />
    
    {{-- Canonical --}}
    <link rel="canonical" href="{{ url()->current() }}" />
</head>
```

### Dynamic SEO (Models)
Using `spatie/laravel-seo` on a Post model:

```php
use Spatie\Seo\Seo;

public function show(Post $post)
{
    seo()
        ->title($post->title)
        ->description($post->excerpt)
        ->image($post->cover_url)
        ->twitter();
        
    return view('posts.show', compact('post'));
}
```

---

## 3. Technical Checklist (Laravel)

- [ ] **Sitemap**: Configure `spatie/laravel-sitemap` to run daily via Scheduler.
- [ ] **Robots.txt**: Ensure `public/robots.txt` exists or use a route to generate it.
- [ ] **Performance**: Run `php artisan optimize` and cache config in production.
- [ ] **Images**: Use `spatie/laravel-medialibrary` for responsive images/WebP conversion.
- [ ] **Trailing Slashes**: Ensure consistent URL structure (Laravel default is no-slash).

---

## 4. Auditing

Since we removed the legacy Python script, use these standard tools:

| Tool | Purpose |
|------|---------|
| **Lighthouse** (Chrome DevTools) | Performance, Accessibility, SEO score |
| **Google Search Console** | Indexing status, Core Web Vitals |
| **Ahrefs / Semrush** | Backlink checking |

> **Note:** We do not include a PHP script for SEO because Google's rendering (JS) is best audited by a browser-based tool like Lighthouse.

---
