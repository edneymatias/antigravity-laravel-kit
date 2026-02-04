---
name: legacy-modernization
description: Modernize legacy Laravel/PHP code. Strangler fig pattern, incremental upgrades, backward compatibility. Use when upgrading old Laravel versions or modernizing legacy patterns.
---

# Legacy Modernization

> Safely modernize legacy code without breaking existing functionality.

## When to Use

- Upgrading Laravel versions (5.x → 11.x)
- Migrating from old PHP patterns
- Replacing deprecated code
- Modernizing architecture
- Adding tests to legacy code

## Core Principle

**Never break what's working.** Use incremental changes with backward compatibility.

---

## 1. The Strangler Fig Pattern

Replace legacy code gradually, not all at once.

### Strategy

```
┌─────────────────────────────────────────┐
│            Your Application             │
├─────────────────────────────────────────┤
│  ┌──────────┐    ┌──────────────────┐  │
│  │  Legacy  │ ←→ │  New Component   │  │
│  │   Code   │    │   (Facade/API)   │  │
│  └──────────┘    └──────────────────┘  │
│       ↓                   ↓             │
│  (Gradually          (New code         │
│   deprecated)         takes over)       │
└─────────────────────────────────────────┘
```

### Implementation

```php
// Phase 1: Create facade over legacy
class PaymentFacade
{
    public function __construct(
        private LegacyPaymentProcessor $legacy,
    ) {}
    
    public function process(Order $order): PaymentResult
    {
        // Wrap legacy in clean interface
        return $this->legacy->doPayment($order->toArray());
    }
}

// Phase 2: Create new implementation
class ModernPaymentService implements PaymentServiceInterface
{
    public function process(Order $order): PaymentResult
    {
        // Clean, modern implementation
    }
}

// Phase 3: Feature flag for gradual migration
class PaymentFacade
{
    public function process(Order $order): PaymentResult
    {
        if (Feature::active('modern-payments')) {
            return $this->modern->process($order);
        }
        
        return $this->legacy->doPayment($order->toArray());
    }
}

// Phase 4: Remove legacy after validation
```

---

## 2. Laravel Version Upgrades

### Pre-Upgrade Checklist

- [ ] Read official upgrade guide
- [ ] Backup database and code
- [ ] Note all third-party packages
- [ ] Check package compatibility
- [ ] Have comprehensive tests
- [ ] Plan rollback strategy

### Upgrade Process

```bash
# Step 1: Update composer.json
# Based on Laravel upgrade guide

# Step 2: Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer clear-cache

# Step 3: Update dependencies
composer update

# Step 4: Publish vendor files
php artisan vendor:publish --tag=laravel-assets --force

# Step 5: Run migrations
php artisan migrate

# Step 6: Run tests
php artisan test
```

### Common Breaking Changes

| From | To | Change |
|------|-----|--------|
| 5.x → 8.x | Middleware | `handle($request, Closure $next)` → add `Response` return type |
| 8.x → 9.x | PHP 8.0 | Constructor property promotion available |
| 9.x → 10.x | PHP 8.1 | Enums, readonly properties |
| 10.x → 11.x | PHP 8.2 | Minimal skeleton structure |

---

## 3. Modernizing PHP Patterns

### Old: Arrays for DTOs

```php
// ❌ Legacy: Arrays everywhere
$user = [
    'name' => 'John',
    'email' => 'john@example.com',
];

// ✅ Modern: DTOs with types
class CreateUserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}
```

### Old: No Type Hints

```php
// ❌ Legacy: No types
function calculateTotal($items, $tax) {
    // No idea what $items is
}

// ✅ Modern: Full type hints
function calculateTotal(Collection $items, float $taxRate): Money
{
    // Clear contract
}
```

### Old: Static Helpers

```php
// ❌ Legacy: Static helpers
class Helper
{
    public static function formatMoney($amount) { ... }
}

// ✅ Modern: Service with DI
class MoneyFormatter
{
    public function __construct(
        private LocaleService $locale,
    ) {}
    
    public function format(Money $amount): string { ... }
}
```

### Old: Global State

```php
// ❌ Legacy: Global config access
function getApiUrl() {
    global $config;
    return $config['api_url'];
}

// ✅ Modern: Dependency injection
class ApiClient
{
    public function __construct(
        private string $baseUrl,
    ) {}
}

// Bound in service provider
$this->app->bind(ApiClient::class, fn () => new ApiClient(
    config('services.api.url')
));
```

---

## 4. Adding Tests to Legacy Code

### Characterization Tests

Capture current behavior (even if buggy) before refactoring.

```php
// Legacy method we want to refactor
class LegacyOrderService
{
    public function calculateTotal($order)
    {
        // Complex, untested legacy logic
    }
}

// Characterization test - captures current behavior
test('legacy order total calculation', function () {
    $service = new LegacyOrderService();
    
    // Capture actual output for known inputs
    $result = $service->calculateTotal([
        'items' => [
            ['price' => 100, 'qty' => 2],
            ['price' => 50, 'qty' => 1],
        ],
        'discount' => 10,
    ]);
    
    // Assert current behavior (even if wrong)
    expect($result)->toBe(240.0);
});
```

### Approval Testing

```php
// For complex outputs, use snapshot testing
test('legacy report generation', function () {
    $report = (new LegacyReportService())->generate();
    
    // First run: Creates snapshot
    // Subsequent: Compares against snapshot
    expect($report)->toMatchSnapshot();
});
```

---

## 5. Feature Flags for Safe Rollout

### Implementation

```php
// config/features.php
return [
    'modern_checkout' => env('FEATURE_MODERN_CHECKOUT', false),
    'new_search' => env('FEATURE_NEW_SEARCH', false),
];

// Feature helper
class Feature
{
    public static function active(string $feature): bool
    {
        return config("features.{$feature}", false);
    }
}

// Usage
if (Feature::active('modern_checkout')) {
    return $this->modernCheckout->process($order);
}
return $this->legacyCheckout->process($order);
```

### Gradual Rollout

```php
// Percentage-based rollout
class Feature
{
    public static function active(string $feature, ?User $user = null): bool
    {
        $config = config("features.{$feature}");
        
        if (is_bool($config)) {
            return $config;
        }
        
        // Percentage rollout
        if (is_int($config) && $user) {
            return ($user->id % 100) < $config;
        }
        
        return false;
    }
}

// config/features.php
'modern_checkout' => 25, // 25% of users
```

---

## 6. Database Migrations for Legacy

### Add Without Breaking

```php
// Don't rename columns - add new ones
Schema::table('users', function (Blueprint $table) {
    // ❌ Breaking
    // $table->renameColumn('name', 'full_name');
    
    // ✅ Safe: Add new column
    $table->string('full_name')->nullable();
});

// Backfill data
User::query()->lazyById()->each(function ($user) {
    $user->update(['full_name' => $user->name]);
});

// Later: Remove old column in separate migration
```

### Dual-Write Pattern

```php
// Write to both old and new during transition
class User extends Model
{
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;        // Old
        $this->attributes['full_name'] = $value;   // New
    }
    
    public function getNameAttribute()
    {
        // Read from new, fallback to old
        return $this->full_name ?? $this->attributes['name'];
    }
}
```

---

## 7. Rollback Plan

### Always Have Escape Route

```bash
# Tag before major changes
git tag pre-modernization-v1

# Document rollback steps
# 1. git checkout pre-modernization-v1
# 2. composer install
# 3. php artisan migrate:rollback --step=X
# 4. Update .env if needed
```

### Database Rollback

```php
// Every migration should have down()
public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('full_name');
    });
}
```

---

## 8. Modernization Checklist

### Phase 1: Prepare

- [ ] Full test coverage on critical paths
- [ ] Document current behavior
- [ ] Create rollback plan
- [ ] Set up feature flags

### Phase 2: Facade

- [ ] Create interfaces for legacy code
- [ ] Wrap legacy in facades
- [ ] Route through new interfaces

### Phase 3: Implement

- [ ] Build modern implementation
- [ ] Wire up feature flags
- [ ] Dual-run legacy and modern

### Phase 4: Migrate

- [ ] Gradual rollout (10% → 50% → 100%)
- [ ] Monitor for issues
- [ ] Compare outputs

### Phase 5: Cleanup

- [ ] Remove feature flags
- [ ] Remove legacy code
- [ ] Update documentation

---

> **Remember:** Legacy code is code that works. Modernize to make it better, not to prove you can.
