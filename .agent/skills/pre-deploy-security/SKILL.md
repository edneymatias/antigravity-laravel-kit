---
name: pre-deploy-security
description: Security checklist before production deployment. Authentication, authorization, secrets, headers, dependencies. Use when preparing for deploy or conducting final review.
---

# Pre-Deploy Security Checklist

> Verify all security controls before going to production.

## When to Use

- Before deploying to production
- Final security review
- After major feature completion
- Periodic security audits

---

## 🔒 1. Secrets Management

### ❌ NEVER

```php
// Hardcoded secrets
$apiKey = 'sk_live_xxxxx';
$password = 'mypassword123';
```

### ✅ ALWAYS

```php
// Environment variables
$apiKey = config('services.stripe.key');
```

### Checklist

- [ ] No hardcoded API keys, tokens, or passwords
- [ ] All secrets in `.env` (not committed)
- [ ] `.env.example` has placeholder values only
- [ ] Production secrets in hosting platform (Forge, Vapor)
- [ ] `APP_KEY` is unique per environment
- [ ] Database credentials not in code

### Verify

```bash
# Search for potential hardcoded secrets
grep -rn "password\s*=" --include="*.php" app/
grep -rn "api_key\s*=" --include="*.php" app/
grep -rn "secret\s*=" --include="*.php" app/
```

---

## 🛡️ 2. Authentication

### Checklist

- [ ] Using Laravel Sanctum for API/SPA
- [ ] Passwords hashed with `Hash::make()` (bcrypt default)
- [ ] Rate limiting on login (`RateLimiter`)
- [ ] `auth` middleware on protected routes
- [ ] Password reset tokens expire
- [ ] Email verification if required

### Verify

```bash
# Check routes without auth middleware
php artisan route:list --except-vendor | grep -v "auth"
```

---

## 🔑 3. Authorization

### Checklist

- [ ] Policies defined for all models
- [ ] `$this->authorize()` in controllers
- [ ] `@can` directives in Blade views
- [ ] No hardcoded role checks (use Gates/Policies)
- [ ] `FilamentUser` implemented for admin access
- [ ] IDOR protection (check ownership)

### Verify

```php
// Every controller action that modifies data should have:
$this->authorize('update', $post);
// or
Gate::authorize('admin-access');
```

---

## 📥 4. Input Validation

### Checklist

- [ ] All user input validated via Form Request
- [ ] File uploads validated (type, size, content)
- [ ] Never use `$request->all()` for mass assignment
- [ ] SQL queries use bindings (no concatenation)
- [ ] JSON input validated

### Verify

```bash
# Check for dangerous patterns
grep -rn '$request->all()' app/
grep -rn "DB::raw" app/
```

---

## 📤 5. Output Encoding (XSS)

### Checklist

- [ ] Using `{{ }}` for all user content
- [ ] `{!! !!}` only for trusted/sanitized HTML
- [ ] JSON responses properly encoded
- [ ] File download names sanitized

### Verify

```bash
# Find raw output
grep -rn "{!!" resources/views/
```

---

## 🍪 6. Session & Cookies

### Checklist

- [ ] `SESSION_SECURE_COOKIE=true` in production
- [ ] `session.http_only=true`
- [ ] `session.same_site=lax` or `strict`
- [ ] Session regenerated on login
- [ ] Session invalidated on logout

### config/session.php

```php
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
```

---

## 🌐 7. HTTP Security Headers

### Checklist

- [ ] HTTPS enforced (`APP_URL=https://...`)
- [ ] HSTS header configured
- [ ] X-Frame-Options set
- [ ] X-Content-Type-Options set
- [ ] Content-Security-Policy (if applicable)

### Laravel Middleware Example

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, Closure $next)
{
    $response = $next($request);
    
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    return $response;
}
```

---

## 📦 8. Dependencies

### Checklist

- [ ] `composer audit` clean (no vulnerabilities)
- [ ] Dependencies up to date
- [ ] `composer.lock` committed
- [ ] No dev dependencies in production

### Verify

```bash
composer audit
composer outdated --direct
```

---

## ⚙️ 9. Configuration

### Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] Error pages don't show stack traces
- [ ] `LOG_LEVEL=warning` or higher
- [ ] `env()` only used in config files

### Verify

```bash
# Check for env() in non-config files
grep -rn "env(" app/ --include="*.php"
```

---

## 🚦 10. Rate Limiting

### Checklist

- [ ] Login/register rate limited
- [ ] API endpoints rate limited
- [ ] Expensive operations limited

### RouteServiceProvider

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

---

## 📋 Quick Verification Script

```bash
#!/bin/bash
echo "=== Pre-Deploy Security Check ==="

echo -e "\n1. Checking for hardcoded secrets..."
grep -rn "password\s*=\s*['\"]" --include="*.php" app/ config/

echo -e "\n2. Checking for raw output..."
grep -rn "{!!" resources/views/

echo -e "\n3. Checking for dangerous queries..."
grep -rn "DB::raw" app/
grep -rn '$request->all()' app/

echo -e "\n4. Checking dependencies..."
composer audit

echo -e "\n5. Checking env in non-config..."
grep -rn "env(" app/ --include="*.php"

echo -e "\n=== Done ==="
```

---

## Final Sign-Off

Before deploying, confirm:

- [ ] All checklist items verified
- [ ] `composer audit` clean
- [ ] Tests passing
- [ ] No debug code left
- [ ] Error pages reviewed
- [ ] Monitoring configured

---

> **Remember:** Security review is not optional. One vulnerability can compromise everything.
