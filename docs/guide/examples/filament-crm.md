# 📖 Example: Building a CRM with Filament

> **Scenario**: Building a Customer Relationship Management (CRM) system for a Coffee Shop.
> **Stack**: Laravel 11, Filament 4, MySQL.

---

## 🗺️ Workflow Map

```
/brainstorm → /plan → /create → /test → /deploy
```

---

## 🚀 Step 1: The Idea (/brainstorm)

You have a vague idea. Let the AI help clarify it.

**User**:
```
/brainstorm CRM for coffee shop
```

**AI (Output)**:
- Asks about features: "Do you need loyalty points?", "Inventory management?"
- Produces `BRIEF.md`:
  - **Core**: Customers, Orders, Products.
  - **Admin**: Filament Panel.
  - **Auth**: Multi-user support.

---

## 🛠️ Step 2: Setup Project (/create)

Initialize the application with the right stack.

**User**:
```
/create new laravel app named "CoffeeCRM"
```

**AI (Agent)**:
- Suggests: `laravel/laravel` + `filament/filament`.
- Runs:
  ```bash
  composer create-project laravel/laravel coffee-crm
  cd coffee-crm
  composer require filament/filament:"^4.0"
  php artisan filament:install --panels
  ```

---

## 📋 Step 3: Design Database (/plan)

Define your data structure before coding.

**User**:
```
/plan Customer and Order models with relationships
```

**AI (Project Planner)**:
- Creates `docs/PLAN-crm-models.md`.
- Defines Schema:
  - `Customer`: name, email, phone, loyalty_points.
  - `Order`: customer_id, total, status (pending/completed).
  - `OrderItem`: order_id, product_id, quantity.

---

## 💻 Step 4: Implementation

Build the backend and admin panel.

### 4.1 Backend Models
**User**:
```
@backend-specialist create Customer model with migration and factory
```

**AI**:
- Runs: `php artisan make:model Customer -mf`
- Edits migration (adds fields).
- Edits Model (adds `hasMany(Order::class)`).

### 4.2 Filament Resources
**User**:
```
Create Filament resources for Customer and Order
```

**AI (Filament Expert)**:
- Runs:
  ```bash
  php artisan make:filament-resource Customer --generate
  php artisan make:filament-resource Order --generate
  ```
- **Customizes `CustomerResource.php`**:
  - Adds `TextInput::make('email')->email()`.
  - Adds `TextInput::make('loyalty_points')->numeric()`.
- **Customizes `OrderResource.php`**:
  - Adds `Select::make('customer_id')->relationship('customer', 'name')`.
  - Adds `Repeater` for order items.

---

## 🧪 Step 5: Verification (/test)

Ensure everything works.

**User**:
```
/test create customer flow
```

**AI**:
- Generates `tests/Feature/Filament/CreateCustomerTest.php`.
- Uses `livewire(CreateCustomer::class)` to simulate form submission.
- Verifies database has new customer.

---

## 🚢 Step 6: Deploy (/deploy)

Go live.

**User**:
```
/deploy
```

**AI**:
- Runs tests.
- Optimizes assets (`npm run build`).
- Caches config/routes.
- Pushes to production branch (or triggers Forge deployment).

---

## 🎓 Key Takeaways

1. **Use `/plan`** to define relationships first.
2. **Use `@filament-expert`** (or just ask) for Admin UI tasks.
3. **Use `/test`** to verify critical business logic (like Order totals).
