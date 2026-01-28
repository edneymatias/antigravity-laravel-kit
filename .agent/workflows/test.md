---
description: Run tests using Pest or PHPUnit
---

# /test - Run Laravel Tests

## Workflow Steps

### 1. Run All Tests
```bash
php artisan test --compact
```

### 2. Run Specific Test
```bash
php artisan test --filter=TestClassName
php artisan test --filter=test_method_name
```

### 3. Run with Coverage
```bash
php artisan test --coverage
php artisan test --coverage --min=80
```

### 4. Create New Test
```bash
# Feature test (default)
php artisan make:test CreatePostTest --pest

# Unit test
php artisan make:test PostServiceTest --pest --unit
```

### 5. Pest-Specific Commands
```bash
# Run specific group
./vendor/bin/pest --group=api

# Run in parallel
./vendor/bin/pest --parallel

# Watch mode (if installed)
./vendor/bin/pest --watch
```

### 6. Fix Failing Tests
If tests fail:
1. Read the error message carefully
2. Use `@debugger` for systematic investigation
3. Fix the code or the test
4. Re-run tests

---

## Test Patterns

### Unit Test (Pest)

```php
// tests/Unit/Services/PricingServiceTest.php

describe('PricingService', function () {
    test('calculateTotal applies discount', function () {
        // Arrange
        $service = new PricingService();
        $items = collect([['price' => 100]]);
        
        // Act
        $total = $service->calculateTotal($items, discountPercent: 10);
        
        // Assert
        expect($total)->toBe(90.0);
    });

    test('throws exception for negative quantity', function () {
        $service = new PricingService();
        
        expect(fn () => $service->calculateTotal([['qty' => -1]]))
            ->toThrow(InvalidArgumentException::class);
    });
});
```

### Filament/Livewire Test

```php
// tests/Feature/Livewire/CreatePostTest.php

use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Models\Post;
use function Pest\Livewire\livewire;

it('can create a post via Filament form', function () {
    // Arrange: Create user with access
    $user = User::factory()->create();

    // Act & Assert
    livewire(CreatePost::class)
        ->actingAs($user) 
        ->fillForm([
            'title' => 'New Filament Post',
            'content' => 'Rich editor content',
            'status' => 'published',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Verify Database
    assertDatabaseHas(Post::class, [
        'title' => 'New Filament Post',
        'status' => 'published',
    ]);
});

it('validates required fields', function () {
    $user = User::factory()->create();

    livewire(CreatePost::class)
        ->actingAs($user)
        ->fillForm([
            'title' => '', // Empty title
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});
```

### HTTP/API Test

```php
// tests/Feature/Api/PostControllerTest.php

it('returns list of posts', function () {
    $posts = Post::factory()->count(3)->create();

    $this->getJson('/api/posts')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'title', 'created_at']]
        ]);
});
```
