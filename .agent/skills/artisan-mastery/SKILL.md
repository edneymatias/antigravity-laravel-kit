---
name: artisan-mastery
description: PHP Artisan CLI patterns including make commands, flags, custom commands, and AI-friendly usage.
---

# Artisan Mastery

## Make Commands Reference

### Essential Flags
```bash
# Model with migration, factory, seeder, controller
php artisan make:model Post -mfsc --no-interaction

# Resource controller with form requests
php artisan make:controller PostController --resource --requests

# Pest test (not PHPUnit)
php artisan make:test CreatePostTest --pest

# Filament resource with generated form/table
php artisan make:filament-resource Post --generate
```

### Full Make Commands List
| Command | Purpose | Common Flags |
|---------|---------|--------------|
| `make:model` | Eloquent model | `-m` (migration), `-f` (factory), `-s` (seeder), `-c` (controller) |
| `make:controller` | HTTP controller | `--resource`, `--api`, `--invokable`, `--requests` |
| `make:migration` | Database migration | `--create=table`, `--table=table` |
| `make:request` | Form request validation | - |
| `make:policy` | Authorization policy | `--model=Post` |
| `make:job` | Queue job | `--sync` |
| `make:event` | Event class | - |
| `make:listener` | Event listener | `--event=EventName` |
| `make:mail` | Mailable class | `--markdown=emails.template` |
| `make:notification` | Notification | `--markdown=notifications.template` |
| `make:command` | Console command | - |
| `make:livewire` | Livewire component | `--inline`, `--test` |

## AI-Friendly Usage

### Always Non-Interactive
```bash
# ✅ Always pass --no-interaction for AI agents
php artisan make:model Post -mfsc --no-interaction

# ✅ Check available options first
php artisan make:model --help
```

### Verify Command Exists
```bash
# List all make commands
php artisan list make

# Get specific command help
php artisan help make:model
```

## Custom Commands

### Command Structure
```php
class PublishPosts extends Command
{
    protected $signature = 'posts:publish {--dry-run : Preview without publishing} {--limit=10 : Max posts to publish}';
    
    protected $description = 'Publish scheduled posts';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $this->info('Dry run mode');
        }
        
        return Command::SUCCESS;
    }
}
```

### Naming Conventions
```
✅ posts:publish      (noun:verb)
✅ cache:clear        (noun:verb)  
✅ queue:work         (noun:verb)
❌ publish-posts      (kebab-case)
❌ PublishPosts       (PascalCase)
```

## Common Workflows

### Scaffold Full Feature
```bash
# 1. Create model with everything
php artisan make:model Post -mfsc --no-interaction

# 2. Create form requests
php artisan make:request StorePostRequest --no-interaction
php artisan make:request UpdatePostRequest --no-interaction

# 3. Create policy
php artisan make:policy PostPolicy --model=Post --no-interaction

# 4. Create tests
php artisan make:test CreatePostTest --pest --no-interaction
php artisan make:test UpdatePostTest --pest --no-interaction
```

### List Routes
```bash
# All routes
php artisan route:list

# Filter by path
php artisan route:list --path=api

# Filter by name
php artisan route:list --name=posts
```
