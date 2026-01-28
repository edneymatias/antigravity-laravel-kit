---
description: Enhance existing Laravel code with optimizations and improvements
---

# /enhance - Enhance Laravel Code

## Workflow Steps

### 1. Identify Enhancement Target
Ask:
- What needs to be enhanced?
- What's the specific issue or goal?
- Performance? Readability? Features?

### 2. Analyze Current Code
- Run tests to ensure baseline works
- Check for anti-patterns
- Profile if performance-related

### 3. Enhancement Types

#### Performance Enhancement
```bash
# Check for N+1 queries
# Use Debugbar to identify slow queries
# Add eager loading, indexes, caching
```

#### Code Quality Enhancement
```bash
# Run Pint for code style
./vendor/bin/pint

# Run static analysis (if configured)
./vendor/bin/phpstan analyse
```

#### Refactoring
- Extract to services for complex logic
- Create Blade components for repeated UI
- Add proper type hints

### 4. Apply Enhancement
- Make incremental changes
- Test after each change
- Keep commits focused

### 5. Verify Enhancement
```bash
# Run tests
php artisan test

# Check code style
./vendor/bin/pint --test
```

### 6. Document Changes
- What was enhanced?
- Why was it changed?
- Any breaking changes?
