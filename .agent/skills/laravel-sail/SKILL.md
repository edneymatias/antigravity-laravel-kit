---
name: laravel-sail
description: Laravel Sail Docker development environment patterns.
---

# Laravel Sail

## Overview

Laravel Sail is a lightweight CLI for interacting with Laravel's Docker development environment.

## Installation

### New Project
```bash
curl -s "https://laravel.build/my-app" | bash
cd my-app
./vendor/bin/sail up -d
```

### Existing Project
```bash
composer require laravel/sail --dev
php artisan sail:install
```

## Basic Commands

### Start/Stop
```bash
# Start in background
./vendor/bin/sail up -d

# Stop
./vendor/bin/sail down

# Stop and remove volumes
./vendor/bin/sail down -v
```

### Running Commands
```bash
# Artisan
./vendor/bin/sail artisan migrate

# Composer
./vendor/bin/sail composer require package

# NPM
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev

# PHP
./vendor/bin/sail php script.php

# Tinker
./vendor/bin/sail tinker
```

### Testing
```bash
./vendor/bin/sail test
./vendor/bin/sail pest
```

## Shell Alias

Add to `~/.bashrc` or `~/.zshrc`:
```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

Then use:
```bash
sail up -d
sail artisan migrate
sail npm run dev
```

## Services

### Available Services
- MySQL, PostgreSQL, MariaDB
- Redis, Memcached
- Meilisearch
- MinIO (S3-compatible storage)
- Mailpit (email testing)
- Selenium (browser testing)

### Add Services
```bash
php artisan sail:add
# Select services interactively
```

### Environment Variables
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
```

## Docker Compose

### Custom Configuration
```bash
php artisan sail:publish
```

Edit `docker-compose.yml` for custom configuration.

### Build Custom Image
```bash
./vendor/bin/sail build --no-cache
```

## Common Issues

### Port Conflicts
```yaml
# docker-compose.yml
services:
  laravel.test:
    ports:
      - '${APP_PORT:-8080}:80'  # Change 80 to 8080
```

### Permission Issues
```bash
sail root-shell
chown -R sail:sail /var/www/html/storage
```

## Best Practices

✅ Use Sail for local development
✅ Add shell alias for convenience
✅ Match production services locally

❌ Don't use Sail in production
❌ Don't commit Docker volumes
