---
name: eloquent-expert
description: Advanced Eloquent ORM patterns including relationships, eager loading, factories, and query optimization.
---

# Eloquent Expert

## Relationships

### Define with Return Types
```php
public function posts(): HasMany
{
    return $this->hasMany(Post::class);
}

public function author(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}

public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class)->withTimestamps();
}
```

### Relationship Types
| Type | Model A | Model B | Use Case |
|------|---------|---------|----------|
| `hasOne` | User | Profile | One-to-one |
| `hasMany` | User | Posts | One-to-many |
| `belongsTo` | Post | User | Inverse |
| `belongsToMany` | Post | Tags | Many-to-many (pivot) |
| `hasManyThrough` | Country | Posts | Through Users |
| `morphMany` | Post/Video | Comments | Polymorphic |

## Eager Loading (N+1 Prevention)

```php
// ❌ N+1 Problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Query per iteration!
}

// ✅ Eager Load
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // No extra queries
}

// ✅ Nested Eager Load
$posts = Post::with(['author', 'comments.user'])->get();

// ✅ Lazy Eager Load (after query)
$posts = Post::all();
$posts->load('author');
```

## Factories

### Define Factory
```php
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'user_id' => User::factory(),
            'published_at' => null,
        ];
    }
    
    public function published(): static
    {
        return $this->state(fn (array $attrs) => [
            'published_at' => now(),
        ]);
    }
}
```

### Use Factory
```php
// Single
$post = Post::factory()->create();

// Multiple
$posts = Post::factory()->count(5)->create();

// With state
$post = Post::factory()->published()->create();

// With relationship
$post = Post::factory()
    ->has(Comment::factory()->count(3))
    ->create();
```

## Query Scopes

```php
// Local scope
public function scopePublished(Builder $query): void
{
    $query->whereNotNull('published_at');
}

public function scopeByAuthor(Builder $query, User $user): void
{
    $query->where('user_id', $user->id);
}

// Usage
Post::published()->byAuthor($user)->get();
```

## Attribute Casting

```php
protected $casts = [
    'metadata' => 'array',
    'is_active' => 'boolean',
    'published_at' => 'datetime',
    'price' => 'decimal:2',
    'status' => PostStatus::class, // Enum
];
```

## Query Optimization

```php
// Select specific columns
User::select(['id', 'name', 'email'])->get();

// Use chunk for large datasets
Post::chunk(100, function ($posts) {
    foreach ($posts as $post) {
        // Process...
    }
});

// Use cursor for memory efficiency
foreach (Post::cursor() as $post) {
    // One at a time, low memory
}
```

## Common Patterns

### Avoid N+1 with withCount
```php
$users = User::withCount('posts')->get();
// Access: $user->posts_count
```

### Update or Create
```php
Post::updateOrCreate(
    ['slug' => $slug],
    ['title' => $title, 'body' => $body]
);
```

### First or Create
```php
$user = User::firstOrCreate(
    ['email' => $email],
    ['name' => $name]
);
```
