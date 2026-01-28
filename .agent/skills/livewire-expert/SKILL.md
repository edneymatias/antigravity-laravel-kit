---
name: livewire-expert
description: Livewire 3 development patterns including components, wire directives, events, and testing.
---

# Livewire 3 Expert

## Creating Components

```bash
php artisan make:livewire Posts/CreatePost
```

Creates:
- `app/Livewire/Posts/CreatePost.php`
- `resources/views/livewire/posts/create-post.blade.php`

## Component Structure

```php
namespace App\Livewire\Posts;

use Livewire\Component;

class CreatePost extends Component
{
    public string $title = '';
    public string $body = '';
    
    public function mount(): void
    {
        // Initialization
    }
    
    public function save(): void
    {
        $this->validate([
            'title' => 'required|max:255',
            'body' => 'required',
        ]);
        
        Post::create([
            'title' => $this->title,
            'body' => $this->body,
            'user_id' => auth()->id(),
        ]);
        
        $this->dispatch('post-created');
        $this->reset(['title', 'body']);
    }
    
    public function render()
    {
        return view('livewire.posts.create-post');
    }
}
```

## Wire Directives

### Data Binding
```blade
{{-- Deferred (default in v3) --}}
<input type="text" wire:model="title">

{{-- Real-time --}}
<input type="text" wire:model.live="search">

{{-- On blur --}}
<input type="text" wire:model.blur="email">

{{-- Debounced --}}
<input type="text" wire:model.live.debounce.500ms="search">
```

### Actions
```blade
<button wire:click="save">Save</button>
<button wire:click="delete({{ $post->id }})">Delete</button>
<form wire:submit="save">
```

### Loading States
```blade
<button wire:click="save">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>

<div wire:loading.class="opacity-50">
    Content fades during loading
</div>

{{-- Target specific action --}}
<span wire:loading wire:target="save">Saving...</span>
```

### Keyboard Events
```blade
<input wire:keydown.enter="search">
<input wire:keyup.escape="close">
```

## Events

### Dispatching
```php
// From component
$this->dispatch('post-created', id: $post->id);

// To specific component
$this->dispatch('refresh')->to(PostList::class);

// To self
$this->dispatch('refresh')->self();
```

### Listening
```php
// In component
#[On('post-created')]
public function handlePostCreated(int $id): void
{
    $this->posts = Post::all();
}
```

### In JavaScript
```blade
<div x-on:post-created.window="alert('Post created!')">
```

## Lifecycle Hooks

```php
public function mount(User $user): void
{
    // Component initialization
    $this->user = $user;
}

public function hydrate(): void
{
    // On every request
}

public function updated($property): void
{
    // After any property update
}

public function updatedTitle(): void
{
    // After specific property update
}

public function dehydrate(): void
{
    // Before response sent
}
```

## Best Practices

### Always Use wire:key
```blade
@foreach ($posts as $post)
    <div wire:key="post-{{ $post->id }}">
        {{ $post->title }}
    </div>
@endforeach
```

### Validate and Authorize
```php
public function delete(Post $post): void
{
    // Treat like HTTP request!
    $this->authorize('delete', $post);
    $post->delete();
}
```

### Single Root Element
```blade
{{-- ✅ Correct --}}
<div>
    <h1>Title</h1>
    <p>Content</p>
</div>

{{-- ❌ Wrong --}}
<h1>Title</h1>
<p>Content</p>
```

## Testing Livewire

```php
use Livewire\Livewire;

it('can create a post', function () {
    $user = User::factory()->create();
    
    Livewire::actingAs($user)
        ->test(CreatePost::class)
        ->set('title', 'My Post')
        ->set('body', 'Content')
        ->call('save')
        ->assertDispatched('post-created');
    
    expect(Post::count())->toBe(1);
});

it('validates required fields', function () {
    Livewire::test(CreatePost::class)
        ->call('save')
        ->assertHasErrors(['title', 'body']);
});
```

## Alpine.js Integration

Alpine is bundled with Livewire 3. Available plugins: persist, intersect, collapse, focus.

```blade
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open" x-transition>
        Content
    </div>
</div>
```
