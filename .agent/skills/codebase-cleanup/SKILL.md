---
name: codebase-cleanup
description: Codebase cleanup patterns. Technical debt identification, dead code removal, dependency audit. Use when cleaning up large codebases or reducing technical debt.
---

# Codebase Cleanup

> Systematically reduce technical debt and improve codebase health.

## When to Use

- Cleaning up accumulated technical debt
- Removing dead code and unused dependencies
- Preparing codebase for major feature work
- Improving performance and maintainability
- Before major Laravel upgrades

---

## 1. Technical Debt Categories

| Category | Examples | Impact |
|----------|----------|--------|
| **Code Debt** | Duplication, complexity, bad naming | Slow development |
| **Test Debt** | Low coverage, flaky tests | Bugs in production |
| **Dependency Debt** | Outdated packages, vulnerabilities | Security risks |
| **Documentation Debt** | No docs, outdated comments | Onboarding pain |
| **Architecture Debt** | God classes, tight coupling | Hard to change |

---

## 2. Dead Code Removal

### Find Unused Classes

```bash
# Search for classes never instantiated or referenced
# Use PHPStan or Psalm for better detection

# Quick grep for unused classes
grep -r "class \w\+" app/ | cut -d: -f2 | while read class; do
    name=$(echo $class | grep -oP 'class \K\w+')
    count=$(grep -r "$name" app/ --include="*.php" | wc -l)
    if [ $count -eq 1 ]; then
        echo "Possibly unused: $name"
    fi
done
```

### Find Unused Routes

```bash
# List all routes
php artisan route:list --json | jq '.[] | .uri'

# Compare with actual usage in views and controllers
grep -r "route(" resources/views/ --include="*.blade.php"
```

### Find Unused Config

```php
// Check for config keys never accessed
// Review config files for dead entries
php artisan config:clear
php artisan config:show
```

### Remove Unused Dependencies

```bash
# Check for unused Composer packages
composer show --direct

# Remove if not used
composer remove package/name

# Audit for security vulnerabilities
composer audit
```

---

## 3. Dependency Health

### Check Outdated Packages

```bash
# See what's outdated
composer outdated

# See only direct dependencies
composer outdated --direct

# Update all (be careful)
composer update

# Update specific package
composer update vendor/package
```

### Priority Updates

| Priority | Criteria | Action |
|----------|----------|--------|
| 🔴 Critical | Security vulnerabilities | Update immediately |
| 🟠 High | Major version behind | Plan update sprint |
| 🟡 Medium | Minor version behind | Update in next release |
| 🟢 Low | Patch version behind | Update when convenient |

### Laravel-Specific Updates

```bash
# Check Laravel version
php artisan --version

# Laravel upgrade guide
# https://laravel.com/docs/upgrade

# Update Laravel
composer update laravel/framework

# Update all Laravel packages together
composer update laravel/*
```

---

## 4. Code Quality Metrics

### Complexity Analysis

```bash
# Using PHPStan
./vendor/bin/phpstan analyse app/ --level=5

# Check for high complexity with Rector
./vendor/bin/rector process app/ --dry-run
```

### Duplication Detection

```bash
# Using phpcpd (PHP Copy/Paste Detector)
composer require --dev sebastian/phpcpd
./vendor/bin/phpcpd app/

# Output shows duplicate code blocks
```

### Test Coverage

```bash
# Generate coverage report
php artisan test --coverage

# HTML report
php artisan test --coverage-html coverage/
```

---

## 5. Laravel-Specific Cleanup

### Clean Up Routes

```php
// Before: Messy routes file
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
// ... 50 more individual routes

// After: Resource routes
Route::apiResource('users', UserController::class);

// Group related routes
Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::apiResource('users', Admin\UserController::class);
    Route::apiResource('orders', Admin\OrderController::class);
});
```

### Clean Up Migrations

```bash
# Check migration status
php artisan migrate:status

# Squash old migrations (Laravel 8+)
php artisan schema:dump

# Clean up redundant migrations
# (Keep only schema dump + recent migrations)
```

### Clean Up Models

```php
// Check for unused relationships
// Remove relationships that aren't used anywhere

// Check for unused scopes
// Remove scopes that have no callers

// Check for unused accessors/mutators
// Remove if not used
```

### Clean Up Config

```php
// Remove unused config files
// config/abandoned-feature.php

// Remove unused env variables
// .env cleanup

// Consolidate related config
// Move feature-specific config to appropriate files
```

---

## 6. Quick Wins Checklist

### Immediate Actions (< 1 hour each)

- [ ] Run `composer audit` and fix vulnerabilities
- [ ] Run `./vendor/bin/pint` to fix code style
- [ ] Remove obvious dead code (commented blocks)
- [ ] Delete unused blade views
- [ ] Remove unused asset files

### Short Term (< 1 day each)

- [ ] Update outdated dependencies
- [ ] Remove unused routes
- [ ] Consolidate duplicate logic
- [ ] Add missing return types
- [ ] Fix PHPStan level 5 issues

### Medium Term (< 1 week each)

- [ ] Increase test coverage to 60%+
- [ ] Extract god classes into services
- [ ] Document core business logic
- [ ] Squash old migrations

---

## 7. Cleanup Workflow

### Phase 1: Assessment

```bash
# Run all analysis tools
composer audit
./vendor/bin/phpstan analyse
./vendor/bin/pint --test
php artisan test --coverage
```

### Phase 2: Prioritize

Create a cleanup backlog with impact scores:

```markdown
| Item | Impact | Effort | Priority |
|------|--------|--------|----------|
| Security vuln in package X | High | Low | 🔴 Do now |
| Duplicate validation logic | Medium | Medium | 🟠 This sprint |
| Missing tests for OrderService | Medium | High | 🟡 Next sprint |
| Old commented code | Low | Low | 🟢 When available |
```

### Phase 3: Execute

- One cleanup task per PR
- Run full test suite after each cleanup
- Document significant changes

### Phase 4: Maintain

- Add quality gates to CI
- Regular dependency audits
- Periodic cleanup sprints

---

## 8. Prevention

### CI Quality Gates

```yaml
# .github/workflows/quality.yml
- name: Security check
  run: composer audit
  
- name: Code style
  run: ./vendor/bin/pint --test
  
- name: Static analysis
  run: ./vendor/bin/phpstan analyse --level=5
  
- name: Tests
  run: php artisan test
```

### Pre-Commit Hooks

```bash
# Using Husky or pre-commit

# Check code style before commit
./vendor/bin/pint app/ --test
```

---

## Commands Reference

```bash
# Security
composer audit

# Code style
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse

# Tests
php artisan test --coverage

# Outdated packages
composer outdated --direct

# Routes
php artisan route:list

# Unused migrations
php artisan migrate:status
```

---

> **Remember:** Cleanup is ongoing work, not a one-time event. Small, regular cleanup is better than large, occasional cleanups.
