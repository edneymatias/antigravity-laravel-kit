---
name: laravel-security
description: Laravel security patterns including Sanctum, Policies, Gates, and common vulnerability prevention.
---

# Laravel Security

## Built-in Protection

Laravel protects automatically against:

| Attack | Protection | Your Responsibility |
|--------|------------|---------------------|
| SQL Injection | Eloquent uses parameterized queries | Don't use raw queries with user input |
| XSS | Blade `{{ }}` escapes output | Don't use `{!! !!}` with user input |
| CSRF | `@csrf` directive | Include in all forms |
| Mass Assignment | `$fillable`/`$guarded` | Define on all models |

## Authentication (Sanctum)

### API Token Auth
```php
// Create token
$token = $user->createToken('api-token')->plainTextToken;

// Protect routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn () => auth()->user());
});
```

### SPA Auth (Cookie-based)
```php
// config/cors.php
'supports_credentials' => true,

// config/sanctum.php
'stateful' => ['localhost', 'your-spa.test'],
```

## Authorization (Policies)

### Create Policy
```bash
php artisan make:policy PostPolicy --model=Post
```

### Define Policy
```php
class PostPolicy
{
    public function view(User $user, Post $post): bool
    {
        return true; // Anyone can view
    }
    
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
    
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id 
            || $user->is_admin;
    }
}
```

### Use Policy
```php
// In Controller
public function update(Post $post)
{
    $this->authorize('update', $post);
    // ...
}

// In Form Request
public function authorize(): bool
{
    return $this->user()->can('update', $this->post);
}

// In Blade
@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan
```

## Gates

For non-model authorization:
```php
// Define in AuthServiceProvider
Gate::define('access-admin', function (User $user) {
    return $user->is_admin;
});

// Use
if (Gate::allows('access-admin')) {
    // ...
}

// In Blade
@can('access-admin')
    <a href="/admin">Admin Panel</a>
@endcan
```

## Form Requests (Validation)

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
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.required' => 'A title is required.',
        ];
    }
}
```

## Rate Limiting

```php
// Define in RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

// Apply to routes
Route::middleware('throttle:login')->post('/login', LoginController::class);
```

## Security Checklist

### Authentication
- [ ] Using Sanctum for API/SPA
- [ ] Implement `FilamentUser` for Admin access
- [ ] Password hashing (bcrypt default)
- [ ] Rate limiting on login
- [ ] Email verification if needed

### Authorization
- [ ] Policies for all models
- [ ] `authorize()` in controllers
- [ ] `@can` in Blade views

### Input/Output
- [ ] Form Requests for validation
- [ ] `{{ }}` for output (escapes HTML)
- [ ] File upload validation

### Configuration
- [ ] `APP_DEBUG=false` in production
- [ ] Secrets not in version control
- [ ] HTTPS enforced
- [ ] `env()` only in config files

## Common Vulnerabilities to Avoid

```php
// ❌ SQL Injection risk
DB::select("SELECT * FROM users WHERE id = $id");

// ✅ Safe
User::find($id);

// ❌ XSS risk
{!! $userInput !!}

// ✅ Safe
{{ $userInput }}

// ❌ Mass assignment risk
User::create($request->all());

// ✅ Safe
User::create($request->validated());
```
