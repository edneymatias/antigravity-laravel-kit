---
description: Start Laravel development server for local preview
---

# /preview - Preview Laravel Application

## Start Development Server

### Option 1: Artisan Serve
```bash
php artisan serve
# App available at http://127.0.0.1:8000
```

### Option 2: Laravel Sail (Docker)
```bash
./vendor/bin/sail up -d
# App available at http://localhost
```

### Option 3: With Vite (Frontend Assets)

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

## Stop Preview

### Artisan Serve
`Ctrl+C` in terminal

### Sail
```bash
./vendor/bin/sail down
```

### Vite
`Ctrl+C` in terminal

## Common Issues

### Port Already in Use
```bash
# Use different port
php artisan serve --port=8080
```

### Vite Manifest Error
```bash
# Build assets
npm run build
# Or run dev server
npm run dev
```
