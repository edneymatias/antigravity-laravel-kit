---
name: tinker-usage
description: Laravel Tinker best practices for debugging, testing ideas, and data exploration. When and how to use safely.
---

# Tinker Best Practices

## When to Use Tinker

| Use Tinker | Use Tests Instead |
|------------|-------------------|
| Quick debugging | Repeatable verification |
| Data exploration | Creating production data |
| Testing one-liners | Complex logic validation |
| Checking relationships | Testing side effects |

## Safe Usage Rules

### Never Modify Production Data
```php
// ❌ DANGEROUS in production
User::where('role', 'admin')->delete();

// ✅ Read-only exploration
User::where('role', 'admin')->count();
User::where('role', 'admin')->pluck('email');
```

### Use Database Transactions
```php
// Wrap experiments in transactions
DB::beginTransaction();

$user = User::factory()->create();
$user->posts()->createMany([...]);

// Inspect results...
$user->posts()->count();

// Rollback everything
DB::rollBack();
```

## Useful Snippets

### Relationship Debugging
```php
// Check eager loading
User::with('posts')->first()->posts;

// Debug relationship SQL
DB::enableQueryLog();
User::with('posts.comments')->first();
dd(DB::getQueryLog());
```

### Model Inspection
```php
// Get fillable/guarded
(new Post)->getFillable();
(new Post)->getGuarded();

// Get casts
(new Post)->getCasts();

// Get relationships
(new ReflectionClass(Post::class))
    ->getMethods()
    ->filter(fn($m) => $m->class === Post::class)
    ->pluck('name');
```

### Testing Factories
```php
// Preview factory output
Post::factory()->make();
Post::factory()->make(['title' => 'Custom']);

// Test factory states
Post::factory()->published()->make();
```

### Configuration Check
```php
config('app.name');
config('database.default');
config('queue.default');
```

## Alternatives to Tinker

### For Persistent Debugging
- **Telescope**: Full request/query/job inspection
- **Debugbar**: Browser-based debugging
- **Ray**: Desktop debugging app

### For Data Operations
- **Seeders**: Repeatable data creation
- **Feature Tests**: Verify behavior with assertions
- **Artisan Commands**: Scripted data operations

## Tinker Configuration

### Auto-Imported Classes
```php
// config/tinker.php
'alias' => [
    'User' => App\Models\User::class,
    'Post' => App\Models\Post::class,
],
```

### Dangerous Commands Block
```php
// config/tinker.php
'dont_alias' => [
    App\Services\PaymentService::class,
],
```

## IDE Integration

### PsySH Configuration
```php
// ~/.config/psysh/config.php
return [
    'defaultIncludes' => [
        __DIR__.'/tinker_helpers.php',
    ],
    'historySize' => 1000,
];
```
