---
name: laravel-best-practices
description: Laravel development best practices covering service providers, dependency injection, facades, and the Laravel Way.
---

# Laravel Best Practices

## The Laravel Way

Laravel has conventions that make development faster and code more maintainable. Follow these principles.

## Service Providers

### Registration
```php
// Register bindings in boot() or register()
public function register(): void
{
    $this->app->bind(PaymentGateway::class, StripeGateway::class);
}

public function boot(): void
{
    // Event listeners, view composers, etc.
}
```

## Dependency Injection

### Constructor Injection (Preferred)
```php
public function __construct(
    private PaymentService $payments,
    private MailService $mail,
) {}
```

### Method Injection (Controllers)
```php
public function store(StorePostRequest $request, PostService $service): RedirectResponse
{
    $service->create($request->validated());
    return redirect()->route('posts.index');
}
```

## Facades vs Injection

| Use Facade | Use Injection |
|------------|---------------|
| Quick prototyping | Production code |
| View/Blade files | Controllers/Services |
| Simple operations | Complex dependencies |

## Configuration

### Environment Variables
- Use `env()` ONLY in config files
- Use `config()` everywhere else
- Cache config in production: `php artisan config:cache`

```php
// ✅ Correct
config('app.name');

// ❌ Wrong
env('APP_NAME');
```

## Eloquent

### Model Conventions
- Return types on relationships
- `$fillable` or `$guarded` defined
- Scopes for common queries
- Casts for data types

```php
class Post extends Model
{
    protected $fillable = ['title', 'body', 'user_id'];
    
    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at');
    }
}
```

## Controllers

### Keep Controllers Thin
- Use Form Requests for validation
- Use Services for business logic
- Return responses, don't do work

```php
class PostController extends Controller
{
    public function store(StorePostRequest $request, PostService $service): RedirectResponse
    {
        $service->createPost($request->validated());
        return redirect()->route('posts.index');
    }
}
```

## Form Requests

### Validation + Authorization
```php
class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Or policy check
    }
    
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}
```

## Artisan Commands

Use `php artisan make:*` for everything:
- `make:model Post -mfsc` (full stack)
- `make:controller PostController --resource`
- `make:request StorePostRequest`
- `make:test CreatePostTest --pest`
- `make:livewire Posts/CreatePost`

## Testing

- Most tests should be feature tests
- Use factories for test data
- Use Pest for cleaner syntax
- Test behavior, not implementation
