---
description: Create new Laravel features or applications using Artisan and starter kits
---

# /create - Create New Laravel Features

## Workflow Steps

### 1. Clarify Requirements
Ask the user:
- What are you creating? (feature, full app, component?)
- Is this a new Laravel project or existing?
- What stack? (Blade + Livewire, Inertia + React/Vue?)

### 2. For New Laravel Project
```bash
# Create new Laravel project
composer create-project laravel/laravel project-name

# With starter kit (Breeze)
cd project-name
composer require laravel/breeze --dev
php artisan breeze:install blade  # or livewire, react, vue

# Install dependencies
npm install
npm run build
```

### 3. For New Feature
Use Artisan make commands:
```bash
# Model with everything
php artisan make:model Post -mfsc

# Controller
php artisan make:controller PostController --resource

# Livewire component
php artisan make:livewire Posts/CreatePost

# Form Request
php artisan make:request StorePostRequest
```

### 4. Implement the Feature
Follow the agent's guidance based on what you're building:
- Backend logic → `@backend-specialist`
- UI/Frontend → `@frontend-specialist`
- Database → `@database-architect`

### 5. Run Tests
```bash
php artisan test
```

### 6. Format Code
```bash
./vendor/bin/pint
```
