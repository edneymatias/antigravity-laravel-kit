#!/usr/bin/env php
<?php
/**
 * Session Manager - Antigravity Laravel Kit
 * ==========================================
 *
 * Analyzes Laravel project state, detects installed packages, and provides
 * a summary of the current session.
 *
 * Usage:
 *     php .agent/scripts/session_manager.php status
 *     php .agent/scripts/session_manager.php info
 */

// ANSI colors
define('COLOR_GREEN', "\033[92m");
define('COLOR_CYAN', "\033[96m");
define('COLOR_YELLOW', "\033[93m");
define('COLOR_BOLD', "\033[1m");
define('COLOR_END', "\033[0m");

/**
 * Analyze composer.json to detect stack
 */
function analyzeComposer(string $path): array
{
    $composerFile = $path . '/composer.json';
    
    if (!file_exists($composerFile)) {
        return ['type' => 'unknown', 'packages' => []];
    }
    
    $data = json_decode(file_get_contents($composerFile), true);
    
    if (!$data) {
        return ['error' => 'Failed to parse composer.json'];
    }
    
    $require = $data['require'] ?? [];
    $requireDev = $data['require-dev'] ?? [];
    $allPackages = array_merge(array_keys($require), array_keys($requireDev));
    
    $stack = [];
    
    // Core framework
    if (isset($require['laravel/framework'])) {
        $stack[] = 'Laravel ' . ($require['laravel/framework'] ?? '');
    }
    
    // Frontend
    if (in_array('livewire/livewire', $allPackages)) $stack[] = 'Livewire';
    if (in_array('inertiajs/inertia-laravel', $allPackages)) $stack[] = 'Inertia';
    if (in_array('laravel/jetstream', $allPackages)) $stack[] = 'Jetstream';
    if (in_array('laravel/breeze', $allPackages)) $stack[] = 'Breeze';
    
    // Admin
    if (in_array('filament/filament', $allPackages)) $stack[] = 'Filament';
    if (in_array('laravel/nova', $allPackages)) $stack[] = 'Nova';
    
    // Testing
    if (in_array('pestphp/pest', $allPackages)) $stack[] = 'Pest';
    if (in_array('phpunit/phpunit', $allPackages)) $stack[] = 'PHPUnit';
    
    // Database
    if (in_array('laravel/scout', $allPackages)) $stack[] = 'Scout';
    if (in_array('spatie/laravel-medialibrary', $allPackages)) $stack[] = 'Media Library';
    
    // Auth
    if (in_array('laravel/sanctum', $allPackages)) $stack[] = 'Sanctum';
    if (in_array('laravel/passport', $allPackages)) $stack[] = 'Passport';
    
    // DevOps
    if (in_array('laravel/sail', $allPackages)) $stack[] = 'Sail';
    if (in_array('laravel/horizon', $allPackages)) $stack[] = 'Horizon';
    if (in_array('laravel/telescope', $allPackages)) $stack[] = 'Telescope';
    
    // Code quality
    if (in_array('laravel/pint', $allPackages)) $stack[] = 'Pint';
    if (in_array('larastan/larastan', $allPackages)) $stack[] = 'Larastan';
    
    return [
        'name' => $data['name'] ?? basename($path),
        'description' => $data['description'] ?? '',
        'phpVersion' => $require['php'] ?? 'unknown',
        'stack' => $stack,
        'scripts' => array_keys($data['scripts'] ?? []),
    ];
}

/**
 * Count files in project
 */
function countFiles(string $path): array
{
    $exclude = ['.git', 'vendor', 'node_modules', 'storage', '.agent', '.gemini'];
    $total = 0;
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        $relativePath = str_replace($path . '/', '', $file->getPathname());
        $parts = explode('/', $relativePath);
        
        // Skip excluded directories
        $skip = false;
        foreach ($parts as $part) {
            if (in_array($part, $exclude)) {
                $skip = true;
                break;
            }
        }
        
        if (!$skip && $file->isFile()) {
            $total++;
        }
    }
    
    return ['total' => $total];
}

/**
 * Detect Laravel features/modules
 */
function detectFeatures(string $path): array
{
    $features = [];
    
    // Check app directories
    $dirs = [
        'app/Models' => 'Models',
        'app/Http/Controllers' => 'Controllers',
        'app/Livewire' => 'Livewire Components',
        'app/Jobs' => 'Jobs',
        'app/Mail' => 'Mail',
        'app/Notifications' => 'Notifications',
        'app/Policies' => 'Policies',
        'app/Services' => 'Services',
    ];
    
    foreach ($dirs as $dir => $name) {
        $fullPath = $path . '/' . $dir;
        if (is_dir($fullPath)) {
            $count = count(glob($fullPath . '/*.php'));
            if ($count > 0) {
                $features[] = "{$name} ({$count})";
            }
        }
    }
    
    return $features;
}

/**
 * Print status
 */
function printStatus(string $path): void
{
    $info = analyzeComposer($path);
    $stats = countFiles($path);
    $features = detectFeatures($path);
    
    echo "\n" . COLOR_BOLD . "=== Laravel Project Status ===" . COLOR_END . "\n";
    
    echo "\n📁 " . COLOR_BOLD . "Project: " . COLOR_END . ($info['name'] ?? basename($path)) . "\n";
    echo "📂 Path: {$path}\n";
    echo "🐘 PHP: " . ($info['phpVersion'] ?? 'unknown') . "\n";
    
    echo "\n" . COLOR_CYAN . "🔧 Tech Stack:" . COLOR_END . "\n";
    foreach ($info['stack'] ?? [] as $tech) {
        echo "   • {$tech}\n";
    }
    if (empty($info['stack'])) {
        echo "   (No specific packages detected)\n";
    }
    
    echo "\n" . COLOR_GREEN . "✅ Detected Features:" . COLOR_END . "\n";
    foreach ($features as $feat) {
        echo "   • {$feat}\n";
    }
    if (empty($features)) {
        echo "   (No feature files detected)\n";
    }
    
    echo "\n📄 Files: {$stats['total']} total files tracked\n";
    
    // Composer scripts
    if (!empty($info['scripts'])) {
        echo "\n" . COLOR_YELLOW . "📜 Composer Scripts:" . COLOR_END . "\n";
        foreach (array_slice($info['scripts'], 0, 5) as $script) {
            echo "   • composer {$script}\n";
        }
    }
    
    echo "\n" . str_repeat('=', 30) . "\n\n";
}

// Main
$command = $argv[1] ?? 'status';
$path = realpath($argv[2] ?? '.');

if (!file_exists($path . '/artisan')) {
    echo COLOR_YELLOW . "⚠️  Not a Laravel project (artisan not found)\n" . COLOR_END;
}

switch ($command) {
    case 'status':
        printStatus($path);
        break;
    case 'info':
        echo json_encode(analyzeComposer($path), JSON_PRETTY_PRINT) . "\n";
        break;
    default:
        echo "Usage: php session_manager.php [status|info] [path]\n";
}
