---
description: Deploy Laravel application to production
---

# /deploy - Deploy Laravel Application

## Pre-Deploy Checklist

### 1. Run Tests
```bash
php artisan test
```

### 2. Check Dependencies
```bash
composer audit
```

### 3. Build Assets
```bash
npm run build
```

## Deploy Steps

### Using Laravel Forge (Recommended)

1. Connect your repository to Forge
2. Configure deployment script in Forge dashboard
3. Click "Deploy Now" or push to deploy branch

### Manual Deploy Script

```bash
# Enable maintenance mode
php artisan down --refresh=15

# Pull latest code
git pull origin main

# Install dependencies (production)
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue workers
php artisan queue:restart

# Disable maintenance mode
php artisan up
```

## Post-Deploy Verification

- [ ] Application loads correctly
- [ ] Login/logout works
- [ ] Key features function
- [ ] Queue workers running
- [ ] No errors in logs

## Rollback (if needed)

```bash
# Rollback last migration
php artisan migrate:rollback

# Or git revert
git revert HEAD
git push origin main
```
