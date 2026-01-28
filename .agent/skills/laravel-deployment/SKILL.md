---
name: laravel-deployment
description: Laravel deployment patterns for Forge, Vapor, and manual deployment.
---

# Laravel Deployment

## Pre-Deploy Checklist

- [ ] Tests pass: `php artisan test`
- [ ] Dependencies checked: `composer audit`
- [ ] Assets built: `npm run build`
- [ ] `.env` configured for production
- [ ] `APP_DEBUG=false`

## Laravel Forge (Recommended)

### Setup
1. Create server on Forge
2. Connect Git repository
3. Configure environment variables
4. Set up deployment script

### Deployment Script (Forge)
```bash
cd /home/forge/your-app

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan queue:restart

# Build assets if not in CI
npm ci
npm run build
```

## Laravel Vapor (Serverless)

### Setup
```bash
composer require laravel/vapor-cli --dev
./vendor/bin/vapor login
./vendor/bin/vapor init
```

### Deploy
```bash
./vendor/bin/vapor deploy production
```

## Manual Deployment

### Full Script
```bash
# Enable maintenance mode
php artisan down --refresh=15

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue workers
php artisan queue:restart

# Build assets
npm ci
npm run build

# Exit maintenance mode
php artisan up
```

## Zero-Downtime Deploy

### Using Envoyer
- Atomic deployments
- Health checks
- Rollback support

### Key Concepts
1. Deploy to new release folder
2. Run migrations
3. Symlink `current` to new release
4. Restart queue workers

## Environment Configuration

### Required Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-database
DB_USERNAME=your-user
DB_PASSWORD=your-password

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Security
- Never commit `.env`
- Use Forge/Vapor secrets management
- Rotate APP_KEY only with care

## Post-Deploy Verification

- [ ] Application loads
- [ ] Login works
- [ ] Key features function
- [ ] Queue workers running: `php artisan queue:monitor`
- [ ] No errors in logs: `tail -f storage/logs/laravel.log`

## Rollback

### Quick Rollback
```bash
php artisan migrate:rollback
git revert HEAD
```

### Full Rollback (with Forge/Envoyer)
- Use "Rollback" feature in dashboard
- Points to previous release folder

## Production Optimizations

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```
