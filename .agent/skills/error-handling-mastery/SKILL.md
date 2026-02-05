---
name: error-handling-mastery
description: "Laravel-native error handling patterns. Renderable exceptions, Livewire traits strategies, and user-facing notifications."
---

# Error Handling Mastery

> **The Golden Rule:** Exceptions are for exceptional circumstances, but they are not unexpected. Plan for them at the boundaries.

## Core Philosophy: The Laravel Way

Avoid "Safe Action" wrappers or non-standard `Result` objects. Laravel provides a robust exception handling system that integrates directly with HTTP responses and managing user feedback.

1.  **Throw Early**: Check conditions and throw specific exceptions immediately.
2.  **Catch Late (at Boundary)**: Don't catch exceptions in every service method. Let them bubble up to the Controller, Livewire Component, or Job that initiated the flow.
3.  **Renderable Exceptions**: Let the Exception decide how it should be displayed.

---

## 1. Backend: Renderable Exceptions

Instead of catching an exception in a Controller just to return a view or redirect, implement the `render()` method on the Exception itself.

### The Pattern

```php
// app/Exceptions/Subscription/PaymentFailedException.php
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentFailedException extends Exception
{
    public function __construct(
        string $message = 'Payment failed.', 
        public readonly string $gatewayError = ''
    ) {
        parent::__construct($message);
    }

    public function report(): void
    {
        // Log sensitive details here, NOT in the message shown to user
        Log::error('Payment failed', [
             'error' => $this->gatewayError,
             'user_id' => auth()->id()
        ]);
    }

    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        // Redirect back with input and error message
        return back()
            ->withInput()
            ->withErrors(['payment' => $this->getMessage()]);
    }
}
```

### Usage in Service

```php
public function charge(User $user, int $amount)
{
    try {
        $this->gateway->charge($user->stripe_id, $amount);
    } catch (StripeException $e) {
        // Wrap infrastructure error in domain exception
        throw new PaymentFailedException(
            'We could not process your card.', // User friendly
            $e->getMessage() // Developer/Log friendly
        );
    }
}
```

### Usage in Controller

```php
public function store(Request $request, PaymentService $service)
{
    // No try-catch needed! Laravel catches PaymentFailedException 
    // and calls its render() method automatically.
    $service->charge($request->user(), 1000);
    
    return redirect()->route('success');
}
```

---

## 2. Frontend: Livewire & Filament Strategy

In Livewire components, exceptions don't automatically redirect back nicely (often causing a modal crash). You must catch them at the "Action Boundary" (the public method called by the UI).

### The Pattern: `HandlesBoundaryErrors` Trait

Use this trait (or similar logic) to keep components clean.

```php
// app/Traits/HandlesBoundaryErrors.php
namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

trait HandlesBoundaryErrors
{
    protected function handleAction(callable $action, string $successMessage = null): mixed
    {
        try {
            $result = $action();
            
            if ($successMessage) {
                Notification::make()
                    ->success()
                    ->title($successMessage)
                    ->send();
            }
            
            return $result;
        } catch (\DomainException $e) {
            // Business logic errors (anticipated)
            Notification::make()
                ->warning()
                ->title('Attention')
                ->body($e->getMessage())
                ->send();
                
        } catch (\Exception $e) {
            // System errors (unanticipated)
            Log::error('Action failed in component: ' . static::class, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            Notification::make()
                ->danger()
                ->title('Something went wrong')
                ->body('Please try again later or contact support.')
                ->send();
        }
        
        return null;
    }
}
```

### Usage in Livewire/Filament Component

```php
use App\Traits\HandlesBoundaryErrors;

class CreateUser extends Component
{
    use HandlesBoundaryErrors;

    public function create()
    {
        $this->handleAction(function() {
            $this->validate();
            
            // Critical business logic
            $this->service->provisionUser($this->data);
            
        }, 'User created successfully!');
    }
}
```

---

## 3. UX Guidelines: Toast vs Modal vs Page

| Scenario | UX Pattern | Severity |
| :--- | :--- | :--- |
| **Validation Error** | Inline Field Error | Low |
| **Business Rule** (e.g. "Out of Stock") | Warning Toast / Notification | Medium |
| **System Failure** (e.g. DB Down) | Danger Toast ("Try again later") | High |
| **Complete Crash** (404/500) | Full Error Page | Critical |

### Filament Notification Examples

```php
// Success
Notification::make()->success()->title('Saved')->send();

// Warning (User Action required)
Notification::make()
    ->warning()
    ->title('Limit Reached')
    ->body('Please upgrade your plan to add more items.')
    ->actions([
        Action::make('upgrade')->url(route('billing')),
    ])
    ->send();

// Error (System Fault)
Notification::make()->danger()->title('System Error')->send();
```

---

## 4. Logging Strategy

Distinguish between **Operational Noise** and **True Errors**.

- **Don't Log**: Validation errors, expected business rule violations ("Wrong password").
- **Log as Info**: Auditable events ("User deleted account"), degraded states ("Cache miss, falling back to db").
- **Log as Error**: Unexpected system states, third-party API failures, hardware issues.

### Secure Logging
NEVER log full objects that might contain PII or secrets.

```php
// ❌ BAD
Log::error('Payment failed', ['user' => $user]); // Dumps hashed password, PII

// ✅ GOOD
Log::error('Payment failed', ['user_id' => $user->id]);
```

---

## 5. Anti-Patterns

### ❌ The "Swallower"
```php
try {
    $job->run();
} catch (Exception $e) {
    // nothing...
}
```
*Why bad:* The system is in an undefined state, and no one knows it failed.

### ❌ The "Global Catcher"
```php
public function index() {
    try {
        return view('index');
    } catch (Exception $e) {
        return view('error');
    }
}
```
*Why bad:* Hides 404s, prevents global exception handler from reporting to Sentry/Bugsnag.

### ❌ The "Return False"
```php
if (!$service->doSomething()) {
    return redirect()->back()->with('error', 'Failed');
}
```
*Why bad:* Ambiguous. *Why* did it fail? Validation? Database? Network? Throw Exceptions instead of returning booleans for complex operations.
