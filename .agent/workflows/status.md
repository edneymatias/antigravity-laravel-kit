---
description: Check Laravel application status and health
---

# /status - Check Laravel Status

## Application Status

### 1. Environment Check
```bash
php artisan env
```

### 2. Configuration Check
```bash
php artisan config:show app
```

### 3. Database Connection
```bash
php artisan tinker --execute="DB::connection()->getPdo() ? 'Connected' : 'Failed'"
```

### 4. Migration Status
```bash
php artisan migrate:status
```

### 5. Queue Status
```bash
# Check pending jobs
php artisan queue:monitor redis:default

# Or via tinker
php artisan tinker --execute="DB::table('jobs')->count()"
```

### 6. Cache Status
```bash
# Test cache connection
php artisan tinker --execute="Cache::put('test', 'ok', 60); Cache::get('test')"
```

### 7. Route List
```bash
php artisan route:list --compact
```

## Health Check Summary

Run all checks:
```bash
echo "=== Environment ===" && php artisan env
echo "=== Migrations ===" && php artisan migrate:status
echo "=== Routes ===" && php artisan route:list --compact | head -20
```

## Common Issues

| Issue | Check | Fix |
|-------|-------|-----|
| DB not connected | `.env` DB_* vars | Update credentials |
| Migrations pending | `migrate:status` | `migrate` |
| Cache issues | Cache driver | `cache:clear` |
| Config stale | | `config:clear` |
