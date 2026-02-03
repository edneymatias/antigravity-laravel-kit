---
name: api-security
description: API security patterns for Laravel. Rate limiting, headers, CORS, Sanctum tokens, input validation. Use when building or securing API endpoints.
---

# API Security

> Secure your APIs against abuse, injection, and unauthorized access.

## When to Use

- Building REST APIs
- Configuring authentication with Sanctum
- Setting up rate limiting
- Configuring CORS
- Securing webhooks

---

## 1. Authentication with Sanctum

### Token-Based (API clients)

```php
// Create token
$token = $user->createToken('api-token', ['read', 'write'])->plainTextToken;

// Protect routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn () => auth()->user());
    Route::apiResource('posts', PostController::class);
});

// Ability check
if ($user->tokenCan('write')) {
    // Allowed
}
```

### SPA Authentication (Cookie-based)

```php
// config/sanctum.php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1'
)),

// config/cors.php
'supports_credentials' => true,

// Frontend must call /sanctum/csrf-cookie first
await fetch('/sanctum/csrf-cookie');
await fetch('/login', { method: 'POST', credentials: 'include' });
```

### Token Best Practices

```php
// ✅ Short-lived tokens for sensitive operations
$token = $user->createToken('payment', ['payments'])->plainTextToken;

// ✅ Prune expired tokens
$user->tokens()->where('last_used_at', '<', now()->subDays(30))->delete();

// ✅ Revoke on password change
$user->tokens()->delete();
```

---

## 2. Rate Limiting

### Define Limiters

```php
// app/Providers/RouteServiceProvider.php
protected function configureRateLimiting(): void
{
    // General API limit
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip());
    });

    // Strict limit for auth endpoints
    RateLimiter::for('auth', function (Request $request) {
        return Limit::perMinute(5)->by($request->ip());
    });

    // Per-user tier limits
    RateLimiter::for('tiered', function (Request $request) {
        $user = $request->user();
        
        return match ($user?->plan) {
            'premium' => Limit::perMinute(1000)->by($user->id),
            'pro' => Limit::perMinute(100)->by($user->id),
            default => Limit::perMinute(20)->by($request->ip()),
        };
    });
}
```

### Apply to Routes

```php
// routes/api.php
Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['auth:sanctum', 'throttle:tiered'])->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

### Rate Limit Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
Retry-After: 60
```

---

## 3. CORS Configuration

### config/cors.php

```php
return [
    // ✅ Specific domains, not '*'
    'allowed_origins' => [
        'https://app.example.com',
        'https://admin.example.com',
    ],

    // ✅ Specific methods
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],

    // ✅ Only needed headers
    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'Accept',
    ],

    // ✅ Expose rate limit headers
    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
    ],

    // ✅ For cookie-based auth
    'supports_credentials' => true,

    'max_age' => 86400, // 24 hours
];
```

### Environment-Based Config

```php
'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),
```

---

## 4. Security Headers

### Middleware

```php
// app/Http/Middleware/ApiSecurityHeaders.php
class ApiSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '0'); // Modern browsers
        $response->headers->set('Cache-Control', 'no-store');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
```

### Register in Kernel

```php
protected $middlewareGroups = [
    'api' => [
        // ... other middleware
        \App\Http\Middleware\ApiSecurityHeaders::class,
    ],
];
```

---

## 5. Input Validation

### API Form Requests

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
            'status' => ['required', Rule::enum(PostStatus::class)],
        ];
    }
}
```

### Consistent Error Responses

```php
// app/Exceptions/Handler.php
protected function invalidJson($request, ValidationException $exception): JsonResponse
{
    return response()->json([
        'message' => 'Validation failed',
        'errors' => $exception->errors(),
    ], 422);
}
```

---

## 6. Response Security

### Never Expose Internals

```php
// ❌ Bad - Exposes internal structure
return response()->json([
    'user' => $user,  // May include sensitive fields
    'debug' => $exception->getTrace(),
]);

// ✅ Good - Controlled response
return response()->json([
    'user' => new UserResource($user),  // API Resource
]);
```

### API Resources

```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            // Never: password, remember_token, api_token
        ];
    }
}
```

---

## 7. Webhook Security

### Verify Signatures

```php
class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('Stripe-Signature');
        $payload = $request->getContent();
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, 
                $signature, 
                $secret
            );
        } catch (\Exception $e) {
            abort(400, 'Invalid signature');
        }

        // Process event...
    }
}
```

### Webhook Best Practices

- [ ] Verify signatures on all webhooks
- [ ] Use allowlist for webhook IPs (if provider offers)
- [ ] Respond quickly (200 OK), process async
- [ ] Idempotency - handle duplicate deliveries
- [ ] Log webhook events for debugging

---

## 8. API Versioning

### Header-Based (Preferred)

```php
// app/Http/Middleware/ApiVersion.php
public function handle(Request $request, Closure $next)
{
    $version = $request->header('Accept-Version', 'v1');
    $request->attributes->set('api_version', $version);
    
    return $next($request);
}
```

### URL-Based

```php
Route::prefix('v1')->group(function () {
    Route::apiResource('posts', V1\PostController::class);
});

Route::prefix('v2')->group(function () {
    Route::apiResource('posts', V2\PostController::class);
});
```

---

## Quick Checklist

### Before Deploying API

- [ ] Sanctum configured correctly
- [ ] Rate limiting on all endpoints
- [ ] CORS restricted to known domains
- [ ] Security headers applied
- [ ] All input validated
- [ ] API Resources used (no raw models)
- [ ] Webhook signatures verified
- [ ] Sensitive data not in responses
- [ ] Error messages generic
- [ ] Logging configured

---

> **Remember:** APIs are public attack surfaces. Every endpoint is a potential entry point.
