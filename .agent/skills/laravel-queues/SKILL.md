---
name: laravel-queues
description: Laravel Queues, Jobs, Workers, and Horizon patterns. Job design, queue configuration, worker management. Use when implementing background processing or async tasks.
---

# Laravel Queues

> Background processing done right.

## When to Use Queues

| Use Case | Why Queue? |
|----------|-----------|
| Sending emails | Don't block user request |
| Processing payments | Retry on failure |
| Generating reports | Long-running task |
| Syncing with APIs | External failures |
| Image processing | CPU intensive |
| Webhooks | Async notification |

---

## 1. Queue Architecture

```
┌──────────────────────────────────────────────────────────┐
│                      Application                          │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐  │
│  │  Controller │───►│   dispatch  │───►│   Queue     │  │
│  │  (sync)     │    │   (async)   │    │   (Redis)   │  │
│  └─────────────┘    └─────────────┘    └──────┬──────┘  │
│                                               │         │
│  ┌─────────────┐    ┌─────────────┐    ╔═════▼══════╗  │
│  │  Response   │◄───│   Result    │◄───║   Worker   ║  │
│  │  (instant)  │    │  (later)    │    ║ php artisan║  │
│  └─────────────┘    └─────────────┘    ║ queue:work ║  │
│                                        ╚════════════╝  │
└──────────────────────────────────────────────────────────┘
```

---

## 2. Creating Jobs

### Basic Job

```bash
php artisan make:job ProcessOrder
```

```php
class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function handle(PaymentService $payment): void
    {
        $payment->charge($this->order);
    }
}
```

### Dispatching

```php
// Async (queued)
ProcessOrder::dispatch($order);

// With delay
ProcessOrder::dispatch($order)->delay(now()->addMinutes(10));

// On specific queue
ProcessOrder::dispatch($order)->onQueue('payments');

// Sync (for testing)
ProcessOrder::dispatchSync($order);
```

---

## 3. Queue Configuration

### config/queue.php

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => null,
    ],
],

// Different queues for different priorities
'high' => env('REDIS_QUEUE', 'high'),
'default' => env('REDIS_QUEUE', 'default'),
'low' => env('REDIS_QUEUE', 'low'),
```

### Queue Priority

```php
// Dispatch to specific queue
SendWelcomeEmail::dispatch($user)->onQueue('emails');
ProcessRefund::dispatch($order)->onQueue('high');
GenerateReport::dispatch($report)->onQueue('low');
```

### Worker Priority

```bash
# Process high priority first
php artisan queue:work --queue=high,default,low
```

---

## 4. Error Handling

### Retry Configuration

```php
class ProcessPayment implements ShouldQueue
{
    // Retry 5 times
    public int $tries = 5;
    
    // Or timeout-based
    public int $maxExceptions = 3;
    
    // Backoff between retries
    public int $backoff = 60; // seconds
    
    // Or exponential backoff
    public function backoff(): array
    {
        return [1, 5, 10]; // Wait 1s, 5s, 10s
    }
    
    // Timeout
    public int $timeout = 120;
}
```

### Handle Failures

```php
class ProcessPayment implements ShouldQueue
{
    public function handle(): void
    {
        // Job logic
    }
    
    public function failed(Throwable $exception): void
    {
        // Notify admin
        Log::error('Payment failed', [
            'order' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);
        
        // Send alert
        Notification::send($this->order->user, new PaymentFailed($this->order));
    }
}
```

### Retry Specific Exceptions

```php
class ProcessPayment implements ShouldQueue
{
    // Only retry these
    public function retryUntil(): DateTime
    {
        return now()->addHours(1);
    }
    
    // Don't retry these
    public function shouldRetry(Throwable $e): bool
    {
        return !($e instanceof InvalidOrderException);
    }
}
```

---

## 5. Job Batching

### Create Batch

```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

$batch = Bus::batch([
    new ProcessOrder($order1),
    new ProcessOrder($order2),
    new ProcessOrder($order3),
])->then(function (Batch $batch) {
    // All jobs completed successfully
    Log::info('Batch completed', ['id' => $batch->id]);
})->catch(function (Batch $batch, Throwable $e) {
    // First job failure
    Log::error('Batch failed', ['error' => $e->getMessage()]);
})->finally(function (Batch $batch) {
    // All jobs finished (success or fail)
})->dispatch();

// Track batch
$batchId = $batch->id;
```

### Monitor Batch

```php
$batch = Bus::findBatch($batchId);

$batch->totalJobs;       // Total jobs in batch
$batch->pendingJobs;     // Jobs not yet processed
$batch->failedJobs;      // Failed jobs
$batch->progress();      // Percentage complete (0-100)
$batch->finished();      // Boolean: all done?
$batch->cancelled();     // Boolean: was cancelled?
```

### Cancel Batch

```php
// From batch object
$batch->cancel();

// Or from job
public function handle(): void
{
    if ($this->shouldCancel()) {
        $this->batch()->cancel();
        return;
    }
}
```

---

## 6. Job Chaining

### Sequential Execution

```php
// Jobs run one after another
Bus::chain([
    new ValidateOrder($order),
    new ChargePayment($order),
    new ShipOrder($order),
    new SendConfirmation($order),
])->dispatch();
```

### Chain with Catch

```php
Bus::chain([
    new ReserveInventory($order),
    new ChargePayment($order),
    new CreateShipment($order),
])->catch(function (Throwable $e) {
    // Chain failed - handle compensation
    Log::error('Order chain failed', ['error' => $e->getMessage()]);
})->dispatch();
```

---

## 7. Unique Jobs

### Prevent Duplicates

```php
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessPodcast implements ShouldQueue, ShouldBeUnique
{
    public function __construct(
        public Podcast $podcast,
    ) {}
    
    // Unique based on podcast ID
    public function uniqueId(): string
    {
        return $this->podcast->id;
    }
    
    // How long to maintain lock
    public int $uniqueFor = 3600; // 1 hour
}
```

### Unique Until Processing

```php
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

// Unique only while waiting in queue
// Allows same job after processing starts
class UpdateSearchIndex implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    // ...
}
```

---

## 8. Horizon

### Installation

```bash
composer require laravel/horizon
php artisan horizon:install
```

### Configuration

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['high', 'default', 'low'],
            'balance' => 'auto',
            'minProcesses' => 1,
            'maxProcesses' => 10,
            'tries' => 3,
        ],
    ],
],
```

### Commands

```bash
# Start Horizon
php artisan horizon

# Pause processing
php artisan horizon:pause

# Continue processing
php artisan horizon:continue

# Terminate gracefully
php artisan horizon:terminate

# Check status
php artisan horizon:status
```

### Monitoring Metrics

```php
// In Horizon dashboard
// - Jobs per minute
// - Wait times
// - Failed jobs
// - Recent jobs
// - Tags
```

---

## 9. Best Practices

### Do's

| Practice | Reason |
|----------|--------|
| Keep jobs small | Easier to retry |
| Make jobs idempotent | Safe to re-run |
| Use timeouts | Prevent stuck jobs |
| Log job progress | Debug failures |
| Use job tags | Track in Horizon |

### Don'ts

| Anti-Pattern | Problem |
|--------------|---------|
| Large payloads | Serialization issues |
| DB queries in constructor | Stale data |
| Long transactions in jobs | Lock contention |
| Too many retries | Queue backlog |

### Idempotent Job Pattern

```php
class ChargePayment implements ShouldQueue
{
    public function handle(): void
    {
        // Check if already processed
        if ($this->order->isPaid()) {
            return; // Idempotent: safe to call again
        }
        
        // Process payment
        $this->paymentService->charge($this->order);
        
        // Mark as processed
        $this->order->markPaid();
    }
}
```

---

## 10. Testing Jobs

### Fake Queue

```php
use Illuminate\Support\Facades\Queue;

test('order triggers job', function () {
    Queue::fake();
    
    // Create order
    $order = Order::factory()->create();
    
    // Assert job was dispatched
    Queue::assertPushed(ProcessOrder::class, function ($job) use ($order) {
        return $job->order->id === $order->id;
    });
});
```

### Test Job Logic

```php
test('process order charges payment', function () {
    $order = Order::factory()->create();
    $job = new ProcessOrder($order);
    
    // Execute job synchronously
    $job->handle(app(PaymentService::class));
    
    expect($order->fresh()->is_paid)->toBeTrue();
});
```

---

## Commands Reference

```bash
# Work queues
php artisan queue:work
php artisan queue:work --queue=high,default
php artisan queue:work --tries=3 --timeout=60

# Monitor
php artisan queue:monitor redis:default,redis:high

# Failed jobs
php artisan queue:failed
php artisan queue:retry all
php artisan queue:retry 5
php artisan queue:forget 5
php artisan queue:flush

# Horizon
php artisan horizon
php artisan horizon:status
php artisan horizon:terminate
```

---

> **Remember:** Queues are for reliability, not just speed. Design jobs to handle failure gracefully.
