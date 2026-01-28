---
description: Debug Laravel issues using logs, Telescope, and Debugbar
---

# /debug - Debug Laravel Issues

## Workflow Steps

### 1. Reproduce the Issue
- Get exact steps to reproduce
- Note any error messages
- Check if consistent or intermittent

### 2. Check Logs
```bash
# View recent logs
tail -f storage/logs/laravel.log

# View last 100 lines
tail -100 storage/logs/laravel.log
```

### 3. Use Debugbar (Development)
- Check Queries tab for N+1 issues
- Check Exceptions tab for errors
- Check Request tab for input data

### 4. Use Telescope (Development)
```bash
# Access at
http://your-app.test/telescope
```

Check:
- Exceptions tab
- Queries tab
- Logs tab
- Jobs tab (for queue issues)

### 5. Use Tinker
```bash
php artisan tinker

# Test queries
> User::find(1)->posts
> Post::where('status', 'draft')->count()
```

### 6. Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 7. Apply Fix
- Make minimal change
- Write test to prevent regression
- Verify fix works

### 8. Document Root Cause
- What was the cause?
- How was it fixed?
- How to prevent in future?
