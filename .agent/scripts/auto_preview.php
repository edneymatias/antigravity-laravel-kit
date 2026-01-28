#!/usr/bin/env php
<?php
/**
 * Auto Preview - Antigravity Laravel Kit
 * =======================================
 *
 * Starts Laravel development server with automatic configuration.
 *
 * Usage:
 *     php .agent/scripts/auto_preview.php              # Start server
 *     php .agent/scripts/auto_preview.php --port 8080  # Custom port
 *     php .agent/scripts/auto_preview.php --sail       # Use Sail
 *     php .agent/scripts/auto_preview.php --stop       # Stop server
 */

// ANSI colors
define('COLOR_GREEN', "\033[92m");
define('COLOR_RED', "\033[91m");
define('COLOR_YELLOW', "\033[93m");
define('COLOR_CYAN', "\033[96m");
define('COLOR_BOLD', "\033[1m");
define('COLOR_END', "\033[0m");

function printSuccess(string $text): void
{
    echo COLOR_GREEN . "✅ {$text}" . COLOR_END . "\n";
}

function printError(string $text): void
{
    echo COLOR_RED . "❌ {$text}" . COLOR_END . "\n";
}

function printInfo(string $text): void
{
    echo COLOR_CYAN . "ℹ️  {$text}" . COLOR_END . "\n";
}

function fileExists(string $path): bool
{
    return file_exists($path);
}

// Parse arguments
$port = 8000;
$useSail = false;
$stop = false;

foreach ($argv as $i => $arg) {
    if ($arg === '--port' && isset($argv[$i + 1])) {
        $port = (int) $argv[$i + 1];
    }
    if ($arg === '--sail') {
        $useSail = true;
    }
    if ($arg === '--stop') {
        $stop = true;
    }
}

// Check if artisan exists
if (!fileExists('artisan')) {
    printError("Not a Laravel project (artisan not found)");
    exit(1);
}

// Stop server
if ($stop) {
    if ($useSail) {
        printInfo("Stopping Sail...");
        passthru('./vendor/bin/sail down');
    } else {
        printInfo("Stopping artisan serve...");
        // Kill any running artisan serve process
        if (PHP_OS_FAMILY !== 'Windows') {
            exec("pkill -f 'artisan serve'");
        }
    }
    printSuccess("Server stopped");
    exit(0);
}

// Start server
echo COLOR_BOLD . "\n🚀 ANTIGRAVITY LARAVEL KIT - PREVIEW SERVER\n" . COLOR_END;
echo str_repeat('=', 50) . "\n\n";

if ($useSail) {
    if (!fileExists('vendor/bin/sail')) {
        printError("Laravel Sail not installed");
        printInfo("Install with: composer require laravel/sail --dev && php artisan sail:install");
        exit(1);
    }
    
    printInfo("Starting Laravel Sail...");
    printInfo("URL: http://localhost");
    echo "\n";
    passthru('./vendor/bin/sail up');
} else {
    printInfo("Starting Laravel development server...");
    printInfo("URL: http://127.0.0.1:{$port}");
    printInfo("Press Ctrl+C to stop\n");
    
    // Also start Vite if package.json exists
    if (fileExists('package.json')) {
        printInfo("Tip: Run 'npm run dev' in another terminal for Vite\n");
    }
    
    passthru("php artisan serve --port={$port}");
}
