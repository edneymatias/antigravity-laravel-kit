---
name: secure-coding-patterns
description: Secure coding patterns for PHP/Laravel. Input validation, output encoding, database security, error handling. Use when writing new code or reviewing for security.
---

# Secure Coding Patterns

> Write secure code from the start. Prevention is cheaper than remediation.

## When to Use

- Writing new features that handle user input
- Creating API endpoints
- Working with database operations
- Handling file uploads
- Implementing error handling

## Core Principles

| Principle | Application |
|-----------|-------------|
| **Never trust input** | Validate everything from users, APIs, even database |
| **Encode output** | Context-aware encoding prevents XSS |
| **Parameterize queries** | Never concatenate SQL |
| **Fail secure** | On error, deny access |
| **Least privilege** | Minimum required permissions |

---

## 1. Input Validation

### Laravel Form Requests (Always Use)

```php
class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:65535'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
```

### Validation Patterns

```php
// ✅ Strict type validation
'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
'quantity' => ['required', 'integer', 'min:1', 'max:1000'],

// ✅ Enum validation
'status' => ['required', Rule::enum(PostStatus::class)],

// ✅ Conditional validation
'company' => ['required_if:type,business', 'string', 'max:255'],

// ✅ Custom validation with closure
'slug' => [
    'required',
    'string',
    function ($attribute, $value, $fail) {
        if (preg_match('/[^a-z0-9\-]/', $value)) {
            $fail('The slug may only contain lowercase letters, numbers, and dashes.');
        }
    },
],
```

### File Upload Validation

```php
'avatar' => [
    'required',
    'image',                    // Must be image
    'mimes:jpeg,png,webp',      // Allowed types
    'max:2048',                 // Max 2MB
    'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
],

'document' => [
    'required',
    'file',
    'mimes:pdf,doc,docx',
    'max:10240',                // Max 10MB
],
```

---

## 2. Output Encoding (XSS Prevention)

### Blade Escaping

```php
// ✅ SAFE - Auto-escaped
{{ $userInput }}

// ❌ DANGEROUS - Raw HTML, only for trusted content
{!! $trustedHtml !!}

// ✅ When you MUST render HTML, sanitize first
{!! clean($userInput) !!}  // Using mews/purifier package
```

### Safe Patterns

```php
// ✅ Safe: escaped by default
<p>{{ $post->title }}</p>
<a href="{{ route('posts.show', $post) }}">View</a>

// ✅ Safe: JSON encoding for JS
<script>
    const data = @json($safeData);
</script>

// ❌ NEVER: User input in raw attributes
<a href="{!! $userUrl !!}">  // XSS risk!

// ✅ Safe: validate URL scheme
<a href="{{ Str::startsWith($url, ['http://', 'https://']) ? $url : '#' }}">
```

### Livewire Considerations

```php
// ✅ Livewire escapes by default
<div>{{ $this->userInput }}</div>

// ❌ Be careful with wire:model on contenteditable
<div contenteditable wire:model="content"></div>  // Risk!

// ✅ Use textarea instead
<textarea wire:model="content"></textarea>
```

---

## 3. Database Security

### Eloquent (Safe by Default)

```php
// ✅ SAFE - Parameterized automatically
User::where('email', $email)->first();
User::find($id);
Post::whereIn('id', $ids)->get();

// ✅ SAFE - Using bindings
DB::select('SELECT * FROM users WHERE email = ?', [$email]);

// ❌ DANGEROUS - String concatenation
DB::select("SELECT * FROM users WHERE email = '$email'");  // SQL Injection!
```

### Raw Queries (When Needed)

```php
// ✅ SAFE - Named bindings
$results = DB::select(
    'SELECT * FROM posts WHERE status = :status AND user_id = :user',
    ['status' => 'published', 'user' => $userId]
);

// ✅ SAFE - whereRaw with bindings
Post::whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])->get();

// ❌ DANGEROUS - Variable in raw
Post::whereRaw("title LIKE '%$search%'")->get();  // SQL Injection!
```

### Mass Assignment Protection

```php
class User extends Model
{
    // ✅ Explicit fillable (preferred)
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    
    // Never allow mass assignment of:
    // - is_admin
    // - role
    // - email_verified_at
    // - remember_token
}

// ✅ SAFE - Using validated data
User::create($request->validated());

// ❌ DANGEROUS - All input
User::create($request->all());  // Mass assignment risk!
```

---

## 4. Error Handling Security

### Production Error Messages

```php
// config/app.php
'debug' => env('APP_DEBUG', false),  // MUST be false in production

// Custom error handler
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            // ✅ Generic message, no stack trace
            return response()->json([
                'message' => 'An error occurred.',
            ], 500);
        }
        
        return parent::render($request, $e);
    }
}
```

### Logging Without Leaking

```php
// ✅ Log details server-side, generic message to user
try {
    $this->processPayment($order);
} catch (PaymentException $e) {
    Log::error('Payment failed', [
        'order_id' => $order->id,
        'error' => $e->getMessage(),
        // Never log: card numbers, CVV, passwords
    ]);
    
    return back()->withErrors(['payment' => 'Payment could not be processed.']);
}
```

### Never Log Sensitive Data

```php
// ❌ NEVER log these
Log::info('Login attempt', ['password' => $password]);
Log::info('Payment', ['card' => $cardNumber]);

// ✅ Log safely
Log::info('Login attempt', ['email' => $email]);
Log::info('Payment', ['last4' => substr($cardNumber, -4)]);
```

---

## 5. Secrets Management

### Environment Variables

```php
// ✅ CORRECT - Secrets in .env
$apiKey = config('services.stripe.key');

// ❌ NEVER - Hardcoded secrets
$apiKey = 'sk_live_xxxxx';  // Never!

// ✅ Config files reference env()
// config/services.php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
],
```

### Validation at Boot

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    if (app()->isProduction()) {
        $required = ['STRIPE_KEY', 'MAIL_PASSWORD', 'APP_KEY'];
        
        foreach ($required as $key) {
            if (empty(env($key))) {
                throw new RuntimeException("Missing required env: $key");
            }
        }
    }
}
```

---

## 6. Session Security

### Secure Session Configuration

```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),      // HTTPS only
'http_only' => true,                                  // No JS access
'same_site' => 'lax',                                 // CSRF protection
'expire_on_close' => false,
'lifetime' => 120,                                    // 2 hours
```

### Session Regeneration

```php
// ✅ Regenerate after login (Laravel does this)
$request->session()->regenerate();

// ✅ Invalidate on logout
$request->session()->invalidate();
$request->session()->regenerateToken();
```

---

## Quick Reference Checklist

### Before Committing Code

- [ ] All user input validated via Form Request
- [ ] Using `{{ }}` not `{!! !!}` for user content
- [ ] No raw SQL with concatenated variables
- [ ] `$fillable` defined on new models
- [ ] No secrets hardcoded
- [ ] Sensitive data not logged
- [ ] Error messages generic for users

### File Uploads

- [ ] MIME type validated
- [ ] File size limited
- [ ] Stored outside public (or with random names)
- [ ] Original filename not trusted

---

> **Remember:** Laravel provides excellent security defaults. Your job is to NOT break them.
