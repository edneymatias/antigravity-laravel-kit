---
name: pest-testing
description: Pest PHP testing framework patterns for Laravel applications.
---

# Pest Testing

## Creating Tests

```bash
# Feature test (recommended for most tests)
php artisan make:test CreatePostTest --pest

# Unit test
php artisan make:test PostServiceTest --pest --unit
```

## Test Syntax

### Basic Test
```php
it('can create a post', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/posts', [
            'title' => 'My Post',
            'body' => 'Content',
        ]);
    
    $response->assertRedirect('/posts');
    expect(Post::count())->toBe(1);
});
```

### Test with Setup
```php
beforeEach(function () {
    $this->user = User::factory()->create();
});

it('requires authentication', function () {
    $this->post('/posts', ['title' => 'Test'])
        ->assertRedirect('/login');
});

it('creates a post when authenticated', function () {
    $this->actingAs($this->user)
        ->post('/posts', ['title' => 'Test', 'body' => 'Content'])
        ->assertRedirect('/posts');
});
```

## Expectations (expect())

```php
// Basic
expect($value)->toBe(5);
expect($value)->toBeTrue();
expect($value)->toBeFalse();
expect($value)->toBeNull();

// Collections
expect($array)->toHaveCount(3);
expect($array)->toContain('item');
expect($array)->toHaveKey('key');

// Types
expect($value)->toBeString();
expect($value)->toBeArray();
expect($value)->toBeInstanceOf(Post::class);

// Chaining
expect($user)
    ->name->toBe('John')
    ->email->toContain('@');
```

## HTTP Testing

```php
it('shows posts list', function () {
    Post::factory()->count(3)->create();
    
    $this->get('/posts')
        ->assertOk()
        ->assertViewIs('posts.index')
        ->assertViewHas('posts');
});

it('validates required fields', function () {
    $this->actingAs(User::factory()->create())
        ->post('/posts', [])
        ->assertSessionHasErrors(['title', 'body']);
});

it('redirects guests', function () {
    $this->post('/posts', ['title' => 'Test'])
        ->assertRedirect('/login');
});
```

## Database Testing

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores post in database', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user)->post('/posts', [
        'title' => 'My Title',
        'body' => 'My Body',
    ]);
    
    $this->assertDatabaseHas('posts', [
        'title' => 'My Title',
        'user_id' => $user->id,
    ]);
});
```

## Livewire Testing

```php
use Livewire\Livewire;

it('can increment counter', function () {
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1);
});

it('validates input', function () {
    Livewire::test(CreatePost::class)
        ->set('title', '')
        ->call('save')
        ->assertHasErrors(['title' => 'required']);
});

it('emits event on save', function () {
    Livewire::test(CreatePost::class)
        ->set('title', 'Test')
        ->call('save')
        ->assertDispatched('post-created');
});
```

## Factories in Tests

```php
it('uses factories correctly', function () {
    // Basic
    $user = User::factory()->create();
    
    // With state
    $admin = User::factory()->admin()->create();
    
    // With relationship
    $post = Post::factory()
        ->has(Comment::factory()->count(3))
        ->create();
    
    expect($post->comments)->toHaveCount(3);
});
```

## Running Tests

```bash
# Run all tests
php artisan test

# With coverage
php artisan test --coverage

# Filter by name
php artisan test --filter=CreatePostTest

# Parallel (faster)
php artisan test --parallel
```

## Best Practices

✅ Use `it()` for readable test names
✅ Use `expect()` for assertions
✅ Use factories for all test data
✅ Use RefreshDatabase trait
✅ Test behavior, not implementation
✅ One assertion per test when possible

❌ Don't test Laravel framework code
❌ Don't use hardcoded IDs
❌ Don't create data manually
