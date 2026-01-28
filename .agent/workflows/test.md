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
