---
name: code-refactoring
description: Code refactoring techniques for PHP/Laravel. SOLID principles, code smells, safe refactoring patterns. Use when improving code quality without changing behavior.
---

# Code Refactoring

> Improve code structure without changing external behavior.

## When to Use

- Improving readability and maintainability
- Reducing code duplication
- Simplifying complex logic
- Preparing code for new features
- Addressing code smells

## Core Principles

| Principle | Application |
|-----------|-------------|
| **Small steps** | One change at a time, keep tests green |
| **Tests first** | Have tests before refactoring |
| **No behavior change** | External API stays the same |
| **Commit often** | Easy to revert if something breaks |

---

## 1. Common Code Smells in Laravel

### Long Methods

```php
// ❌ Too long - doing too much
public function processOrder(Order $order)
{
    // 100+ lines of validation, calculation, notification...
}

// ✅ Extract methods
public function processOrder(Order $order)
{
    $this->validateOrder($order);
    $total = $this->calculateTotal($order);
    $this->chargePayment($order, $total);
    $this->sendConfirmation($order);
}
```

### God Classes

```php
// ❌ God class - too many responsibilities
class OrderService
{
    public function create() { ... }
    public function calculateTax() { ... }
    public function sendEmail() { ... }
    public function generatePdf() { ... }
    public function syncInventory() { ... }
}

// ✅ Single responsibility
class OrderService { /* CRUD only */ }
class TaxCalculator { /* Tax logic */ }
class OrderNotificationService { /* Emails */ }
class InvoicePdfGenerator { /* PDF generation */ }
```

### Feature Envy

```php
// ❌ Method uses another object's data more than its own
class OrderController
{
    public function show(Order $order)
    {
        $total = $order->items->sum('price') * (1 + $order->tax_rate);
        $discount = $order->coupon ? $order->coupon->discount : 0;
        $final = $total - $discount;
    }
}

// ✅ Move logic to the object that owns the data
class Order
{
    public function getTotal(): float
    {
        return $this->subtotal * (1 + $this->tax_rate) - $this->discount;
    }
}
```

### Primitive Obsession

```php
// ❌ Using primitives for domain concepts
public function createUser(string $email, string $phone, int $cents)

// ✅ Value Objects
public function createUser(Email $email, Phone $phone, Money $amount)

// Value Object example
class Email
{
    public function __construct(private string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email: $value");
        }
    }
    
    public function value(): string
    {
        return $this->value;
    }
}
```

---

## 2. Safe Refactoring Patterns

### Extract Method

```php
// Before
public function handle(Request $request)
{
    // Validate
    if (!$request->has('email')) { ... }
    if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) { ... }
    
    // Process
    $user = User::where('email', $request->email)->first();
    $user->last_login = now();
    $user->save();
    
    // Notify
    Mail::to($user)->send(new LoginNotification());
}

// After
public function handle(Request $request)
{
    $this->validateRequest($request);
    $user = $this->processLogin($request);
    $this->notifyUser($user);
}

private function validateRequest(Request $request): void { ... }
private function processLogin(Request $request): User { ... }
private function notifyUser(User $user): void { ... }
```

### Extract Class

```php
// Before: Controller doing too much
class OrderController
{
    public function store(Request $request)
    {
        // 50 lines of order creation logic
    }
}

// After: Dedicated Action class
class CreateOrderAction
{
    public function execute(CreateOrderDTO $dto): Order
    {
        // Order creation logic
    }
}

class OrderController
{
    public function store(StoreOrderRequest $request, CreateOrderAction $action)
    {
        return $action->execute($request->toDTO());
    }
}
```

### Replace Conditional with Polymorphism

```php
// Before: Switch on type
public function calculateShipping(Order $order): float
{
    return match($order->shipping_type) {
        'standard' => $order->weight * 0.5,
        'express' => $order->weight * 1.5 + 10,
        'overnight' => $order->weight * 3 + 25,
    };
}

// After: Strategy pattern
interface ShippingCalculator
{
    public function calculate(Order $order): float;
}

class StandardShipping implements ShippingCalculator { ... }
class ExpressShipping implements ShippingCalculator { ... }
class OvernightShipping implements ShippingCalculator { ... }

// Usage
$calculator = ShippingCalculatorFactory::make($order->shipping_type);
$cost = $calculator->calculate($order);
```

### Introduce Parameter Object

```php
// Before: Many parameters
public function search(
    ?string $query,
    ?string $category,
    ?float $minPrice,
    ?float $maxPrice,
    ?string $sortBy,
    ?string $sortDir
) { ... }

// After: DTO
class SearchCriteria
{
    public function __construct(
        public ?string $query = null,
        public ?string $category = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public string $sortBy = 'created_at',
        public string $sortDir = 'desc',
    ) {}
}

public function search(SearchCriteria $criteria) { ... }
```

---

## 3. Laravel-Specific Refactorings

### Move Logic to Eloquent

```php
// Before: Logic in controller
$activeUsers = User::where('status', 'active')
    ->where('last_login', '>', now()->subDays(30))
    ->orderBy('name')
    ->get();

// After: Query scope in Model
class User extends Model
{
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->where('last_login', '>', now()->subDays($days));
    }
}

// Usage
$activeUsers = User::active()->recentlyActive()->orderBy('name')->get();
```

### Extract to Form Request

```php
// Before: Validation in controller
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        // 10 more rules...
    ]);
}

// After: Dedicated Form Request
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
        ];
    }
}

public function store(StoreUserRequest $request)
{
    $validated = $request->validated();
}
```

### Extract to Action Class

```php
// Before: Fat controller
public function store(StoreOrderRequest $request)
{
    DB::transaction(function () use ($request) {
        $order = Order::create($request->validated());
        $order->items()->createMany($request->items);
        $order->calculateTotals();
        event(new OrderCreated($order));
    });
}

// After: Action class
class CreateOrderAction
{
    public function __construct(
        private InventoryService $inventory,
        private NotificationService $notify,
    ) {}
    
    public function execute(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $order = Order::create($dto->toArray());
            $order->items()->createMany($dto->items);
            $this->inventory->reserve($order);
            event(new OrderCreated($order));
            return $order;
        });
    }
}
```

---

## 4. Refactoring Workflow

### Step 1: Ensure Tests Exist

```bash
# Check coverage
php artisan test --coverage

# If no tests, write characterization tests first
# These capture current behavior, even if buggy
```

### Step 2: Make One Change

```php
// Change ONE thing at a time
// If extracting 3 methods, do them in 3 commits
```

### Step 3: Run Tests

```bash
php artisan test
```

### Step 4: Commit

```bash
git add .
git commit -m "refactor: extract calculateTotal method from OrderService"
```

### Step 5: Repeat

---

## 5. Tools

### PHPStan (Static Analysis)

```bash
# Find type issues, unused code, logic problems
./vendor/bin/phpstan analyse

# Increase level for stricter checks
./vendor/bin/phpstan analyse --level=8
```

### Pint (Code Style)

```bash
# Auto-fix code style
./vendor/bin/pint
```

### Rector (Automated Refactoring)

```bash
# Install
composer require rector/rector --dev

# Generate config
./vendor/bin/rector init

# Preview changes
./vendor/bin/rector --dry-run

# Apply changes
./vendor/bin/rector
```

---

## Quick Reference

### Before Refactoring

- [ ] Tests exist and pass
- [ ] Understand current behavior
- [ ] Identify specific smell to fix
- [ ] Plan small, incremental steps

### During Refactoring

- [ ] One change at a time
- [ ] Run tests after each change
- [ ] Commit after each successful step
- [ ] Keep refactoring and feature work separate

### After Refactoring

- [ ] All tests still pass
- [ ] Code is more readable
- [ ] No behavior changed
- [ ] Ready for code review

---

> **Remember:** Refactoring is about making code easier to understand and modify. If you're not sure, make the change smaller.
