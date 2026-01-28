---
name: blade-mastery
description: Blade templating patterns including components, slots, directives, and layouts.
---

# Blade Mastery

## Blade Components

### Anonymous Components
```php
// resources/views/components/button.blade.php
<button {{ $attributes->merge(['class' => 'btn btn-primary']) }}>
    {{ $slot }}
</button>

// Usage
<x-button class="btn-lg">Submit</x-button>
```

### Class Components
```bash
php artisan make:component Alert
```

```php
// app/View/Components/Alert.php
class Alert extends Component
{
    public function __construct(
        public string $type = 'info',
        public string $message = '',
    ) {}
    
    public function render()
    {
        return view('components.alert');
    }
}
```

```blade
{{-- resources/views/components/alert.blade.php --}}
<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>
```

## Slots

### Default Slot
```blade
{{-- Component --}}
<div class="card">
    {{ $slot }}
</div>

{{-- Usage --}}
<x-card>
    <p>Card content</p>
</x-card>
```

### Named Slots
```blade
{{-- Component --}}
<div class="card">
    <div class="card-header">{{ $header }}</div>
    <div class="card-body">{{ $slot }}</div>
    <div class="card-footer">{{ $footer }}</div>
</div>

{{-- Usage --}}
<x-card>
    <x-slot:header>Card Title</x-slot:header>
    <p>Card content</p>
    <x-slot:footer>Footer text</x-slot:footer>
</x-card>
```

## Attribute Merging

```blade
{{-- Component --}}
<button {{ $attributes->merge(['class' => 'btn']) }}>
    {{ $slot }}
</button>

{{-- Usage: class="btn btn-primary" (merged) --}}
<x-button class="btn-primary">Click</x-button>
```

### Conditional Classes
```blade
<div {{ $attributes->class(['active' => $isActive]) }}>
```

## Directives

### Control Structures
```blade
@if ($condition)
    {{-- content --}}
@elseif ($other)
    {{-- content --}}
@else
    {{-- content --}}
@endif

@unless ($condition)
    {{-- shown if false --}}
@endunless

@empty ($items)
    <p>No items</p>
@endempty
```

### Loops
```blade
@foreach ($items as $item)
    {{ $item->name }}
    @if ($loop->first) First! @endif
    @if ($loop->last) Last! @endif
@endforeach

@forelse ($items as $item)
    {{ $item->name }}
@empty
    <p>No items</p>
@endforelse
```

### Authentication
```blade
@auth
    Welcome, {{ auth()->user()->name }}
@endauth

@guest
    <a href="/login">Login</a>
@endguest
```

### Authorization
```blade
@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan

@cannot('delete', $post)
    <p>You cannot delete this post</p>
@endcannot
```

## Layouts

### Using Components (Laravel 9+)
```blade
{{-- resources/views/components/layouts/app.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'My App' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{ $slot }}
</body>
</html>

{{-- Page view --}}
<x-layouts.app title="Home">
    <h1>Welcome</h1>
</x-layouts.app>
```

### Using Stacks
```blade
{{-- Layout --}}
<head>
    @stack('styles')
</head>
<body>
    {{ $slot }}
    @stack('scripts')
</body>

{{-- Page --}}
@push('styles')
    <link rel="stylesheet" href="custom.css">
@endpush

@push('scripts')
    <script src="custom.js"></script>
@endpush
```

## Best Practices

✅ Extract repeated elements into components
✅ Use named slots for complex layouts
✅ Merge attributes for flexibility
✅ Use `@stack` for page-specific assets

❌ Don't put logic in views
❌ Don't create giant blade files
❌ Don't skip escaping with `{!! !!}`
