---
name: job-patterns
description: Advanced job patterns for Laravel. Saga pattern, compensating transactions, job pipelines, orchestration. Use when building complex async workflows.
---

# Job Patterns

> Patterns for complex async workflows in Laravel.

## When to Use

- Multi-step business processes
- Distributed transactions
- Compensating actions on failure
- Complex job orchestration
- Long-running workflows

---

## 1. Saga Pattern

Manage multi-step processes with compensation on failure.

### Concept

```
Success Path:
Step 1 → Step 2 → Step 3 → Complete ✅

Failure at Step 3:
Step 1 → Step 2 → Step 3 ❌
              ↓
Compensate 2 ← Compensate 1 ← Failed
```

### Implementation

```php
class OrderSaga
{
    private array $completedSteps = [];
    
    public function execute(Order $order): void
    {
        try {
            $this->reserveInventory($order);
            $this->completedSteps[] = 'inventory';
            
            $this->chargePayment($order);
            $this->completedSteps[] = 'payment';
            
            $this->createShipment($order);
            $this->completedSteps[] = 'shipment';
            
        } catch (Throwable $e) {
            $this->compensate($order);
            throw $e;
        }
    }
    
    private function compensate(Order $order): void
    {
        // Reverse order - LIFO
        foreach (array_reverse($this->completedSteps) as $step) {
            match ($step) {
                'shipment' => $this->cancelShipment($order),
                'payment' => $this->refundPayment($order),
                'inventory' => $this->releaseInventory($order),
            };
        }
    }
}
```

### Database-Tracked Saga

```php
// Migration
Schema::create('saga_executions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('type');
    $table->string('status'); // pending, compensating, completed, failed
    $table->json('data');
    $table->json('completed_steps');
    $table->integer('current_step')->default(0);
    $table->text('error')->nullable();
    $table->timestamps();
});

// Model
class SagaExecution extends Model
{
    protected $casts = [
        'data' => 'array',
        'completed_steps' => 'array',
    ];
}

// Base Saga Class
abstract class Saga
{
    protected SagaExecution $execution;
    
    abstract public function steps(): array;
    
    public function start(array $data): SagaExecution
    {
        $this->execution = SagaExecution::create([
            'id' => Str::uuid(),
            'type' => static::class,
            'status' => 'pending',
            'data' => $data,
            'completed_steps' => [],
        ]);
        
        $this->executeNext();
        
        return $this->execution;
    }
    
    protected function executeNext(): void
    {
        $steps = $this->steps();
        
        if ($this->execution->current_step >= count($steps)) {
            $this->execution->update(['status' => 'completed']);
            return;
        }
        
        $step = $steps[$this->execution->current_step];
        
        // Dispatch step job
        dispatch(new SagaStepJob(
            $this->execution->id,
            $step['action'],
            $step['compensation']
        ));
    }
    
    public function handleStepComplete(string $stepName): void
    {
        $completedSteps = $this->execution->completed_steps;
        $completedSteps[] = $stepName;
        
        $this->execution->update([
            'completed_steps' => $completedSteps,
            'current_step' => $this->execution->current_step + 1,
        ]);
        
        $this->executeNext();
    }
    
    public function handleStepFailed(string $error): void
    {
        $this->execution->update([
            'status' => 'compensating',
            'error' => $error,
        ]);
        
        $this->compensate();
    }
    
    protected function compensate(): void
    {
        $steps = $this->steps();
        
        // Execute compensations in reverse
        foreach (array_reverse($this->execution->completed_steps) as $stepName) {
            $step = collect($steps)->firstWhere('name', $stepName);
            dispatch(new SagaCompensationJob(
                $this->execution->id,
                $step['compensation']
            ));
        }
    }
}
```

### Order Fulfillment Saga

```php
class OrderFulfillmentSaga extends Saga
{
    public function steps(): array
    {
        return [
            [
                'name' => 'reserve_inventory',
                'action' => ReserveInventoryJob::class,
                'compensation' => ReleaseInventoryJob::class,
            ],
            [
                'name' => 'charge_payment',
                'action' => ChargePaymentJob::class,
                'compensation' => RefundPaymentJob::class,
            ],
            [
                'name' => 'create_shipment',
                'action' => CreateShipmentJob::class,
                'compensation' => CancelShipmentJob::class,
            ],
            [
                'name' => 'send_confirmation',
                'action' => SendConfirmationJob::class,
                'compensation' => SendCancellationJob::class,
            ],
        ];
    }
}

// Usage
$saga = new OrderFulfillmentSaga();
$execution = $saga->start([
    'order_id' => $order->id,
    'customer_id' => $customer->id,
]);
```

---

## 2. Pipeline Pattern

Sequential jobs with shared context.

### Laravel Pipeline with Jobs

```php
class OrderPipeline
{
    public function process(Order $order): void
    {
        Pipeline::send($order)
            ->through([
                ValidateOrder::class,
                CalculateTotals::class,
                ApplyDiscounts::class,
                ReserveInventory::class,
                ProcessPayment::class,
            ])
            ->thenReturn();
    }
}

// Each stage
class ValidateOrder
{
    public function handle(Order $order, Closure $next)
    {
        if (!$order->isValid()) {
            throw new InvalidOrderException();
        }
        
        return $next($order);
    }
}
```

### Async Pipeline with Chaining

```php
// For async processing, use job chains
Bus::chain([
    new ValidateOrderJob($order),
    new CalculateTotalsJob($order),
    new ApplyDiscountsJob($order),
    new ReserveInventoryJob($order),
    new ProcessPaymentJob($order),
])->catch(function (Throwable $e) use ($order) {
    // Handle pipeline failure
    event(new OrderPipelineFailed($order, $e));
})->dispatch();
```

---

## 3. Workflow State Machine

Track workflow state through database.

### Migration

```php
Schema::create('workflows', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->morphs('workflowable'); // order_id, order_type
    $table->string('state');
    $table->json('history');
    $table->timestamps();
});
```

### Workflow Model

```php
class Workflow extends Model
{
    protected $casts = [
        'history' => 'array',
    ];
    
    public function transition(string $newState, ?string $reason = null): void
    {
        $oldState = $this->state;
        
        $history = $this->history;
        $history[] = [
            'from' => $oldState,
            'to' => $newState,
            'reason' => $reason,
            'at' => now()->toIso8601String(),
        ];
        
        $this->update([
            'state' => $newState,
            'history' => $history,
        ]);
        
        event(new WorkflowTransitioned($this, $oldState, $newState));
    }
    
    public function canTransitionTo(string $state): bool
    {
        $allowed = $this->getAllowedTransitions();
        return in_array($state, $allowed[$this->state] ?? []);
    }
    
    protected function getAllowedTransitions(): array
    {
        return [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['completed', 'failed', 'cancelled'],
            'completed' => [],
            'failed' => ['processing'], // retry
            'cancelled' => [],
        ];
    }
}
```

### Workflow Trait

```php
trait HasWorkflow
{
    public function workflow(): MorphOne
    {
        return $this->morphOne(Workflow::class, 'workflowable');
    }
    
    public function initWorkflow(string $initialState = 'pending'): Workflow
    {
        return $this->workflow()->create([
            'state' => $initialState,
            'history' => [],
        ]);
    }
    
    public function transitionTo(string $state, ?string $reason = null): void
    {
        if (!$this->workflow->canTransitionTo($state)) {
            throw new InvalidTransitionException(
                "Cannot transition from {$this->workflow->state} to {$state}"
            );
        }
        
        $this->workflow->transition($state, $reason);
    }
}

// Usage
class Order extends Model
{
    use HasWorkflow;
}

$order = Order::create([...]);
$order->initWorkflow('pending');
$order->transitionTo('processing');
```

---

## 4. Scheduled Workflow Steps

For long-running workflows with delays.

```php
class ApprovalWorkflow
{
    public function start(Request $request): void
    {
        $request->update(['status' => 'pending_approval']);
        
        // Notify approvers
        SendApprovalRequest::dispatch($request);
        
        // Schedule reminder
        SendApprovalReminder::dispatch($request)
            ->delay(now()->addHours(24));
        
        // Schedule escalation
        EscalateApproval::dispatch($request)
            ->delay(now()->addHours(48));
    }
    
    public function onApproved(Request $request): void
    {
        // Cancel scheduled jobs
        $this->cancelPendingJobs($request);
        
        $request->update(['status' => 'approved']);
    }
}
```

---

## 5. Job Middleware

Reusable job behavior.

### Rate Limiting

```php
// Job Middleware
class RateLimited
{
    public function handle($job, Closure $next): void
    {
        $key = 'api-calls:' . $job->userId;
        
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $job->release(60); // Retry in 60 seconds
            return;
        }
        
        RateLimiter::hit($key);
        
        $next($job);
    }
}

// Usage in Job
class CallExternalApi implements ShouldQueue
{
    public function middleware(): array
    {
        return [new RateLimited()];
    }
}
```

### Without Overlapping

```php
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateSearchIndex implements ShouldQueue
{
    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->product->id)
        ];
    }
}
```

### Skip When Batch Cancelled

```php
use Illuminate\Queue\Middleware\SkipIfBatchCancelled;

class ProcessPodcast implements ShouldQueue
{
    public function middleware(): array
    {
        return [new SkipIfBatchCancelled()];
    }
}
```

---

## 6. Event-Driven Jobs

Decouple with events.

```php
// Event
class OrderPlaced
{
    public function __construct(
        public Order $order,
    ) {}
}

// Listeners (queued)
class ReserveInventory implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        $this->inventory->reserve($event->order);
    }
}

class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        Mail::to($event->order->customer)->send(
            new OrderConfirmationMail($event->order)
        );
    }
}

// Register in EventServiceProvider
protected $listen = [
    OrderPlaced::class => [
        ReserveInventory::class,
        SendOrderConfirmation::class,
        NotifyWarehouse::class,
    ],
];
```

---

## 7. Filament Integration

### Job Monitoring Widget

```php
class QueueMetricsWidget extends Widget
{
    protected static string $view = 'filament.widgets.queue-metrics';
    
    public function getData(): array
    {
        return [
            'pending' => DB::table('jobs')->count(),
            'failed' => DB::table('failed_jobs')->count(),
            'processed_today' => Cache::get('jobs_processed_today', 0),
        ];
    }
}
```

### Retry Failed Jobs Action

```php
class RetryFailedJob extends Action
{
    public function handle()
    {
        Artisan::call('queue:retry', ['id' => $this->record->id]);
        Notification::make()->success()->title('Job retried')->send();
    }
}
```

---

## 8. Testing Patterns

### Test Saga Compensation

```php
test('saga compensates on payment failure', function () {
    $order = Order::factory()->create();
    
    // Mock payment to fail
    $this->mock(PaymentService::class)
        ->shouldReceive('charge')
        ->andThrow(new PaymentFailedException());
    
    // Mock inventory to verify compensation
    $inventory = $this->mock(InventoryService::class);
    $inventory->shouldReceive('reserve')->once();
    $inventory->shouldReceive('release')->once(); // Compensation
    
    $saga = new OrderFulfillmentSaga($inventory, app(PaymentService::class));
    
    expect(fn() => $saga->execute($order))
        ->toThrow(PaymentFailedException::class);
});
```

### Test Job Chain

```php
test('order chain processes in sequence', function () {
    Queue::fake();
    
    $order = Order::factory()->create();
    
    Bus::chain([
        new ValidateOrderJob($order),
        new ProcessPaymentJob($order),
    ])->dispatch();
    
    Queue::assertChained([
        ValidateOrderJob::class,
        ProcessPaymentJob::class,
    ]);
});
```

---

## Quick Reference

| Pattern | Use When |
|---------|----------|
| **Saga** | Multi-step with compensation |
| **Pipeline** | Sequential processing |
| **State Machine** | Track workflow status |
| **Batch** | Parallel with aggregation |
| **Chain** | Sequential async |
| **Event-Driven** | Decoupled reactions |

---

> **Remember:** Complex workflows need observability. Log state changes and use Horizon for monitoring.
