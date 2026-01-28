#!/usr/bin/env php
<?php
/**
 * Master Checklist Runner - Antigravity Laravel Kit
 * ==================================================
 *
 * Orchestrates Laravel validation tools in priority order.
 * Use this for incremental validation during development.
 *
 * Usage:
 *     php .agent/scripts/checklist.php                    # Run core checks
 *     php .agent/scripts/checklist.php --url <URL>        # Include performance checks
 *
 * Priority Order:
 *     P0: Security (composer audit)
 *     P1: Code Style (Pint)
 *     P2: Static Analysis (PHPStan - if configured)
 *     P3: Tests (Pest/PHPUnit)
 *     P4: Database (Migrations status)
 */

// ANSI colors
define('COLOR_GREEN', "\033[92m");
define('COLOR_RED', "\033[91m");
define('COLOR_YELLOW', "\033[93m");
define('COLOR_CYAN', "\033[96m");
define('COLOR_BOLD', "\033[1m");
define('COLOR_END', "\033[0m");

function printHeader(string $text): void
{
    echo "\n" . COLOR_BOLD . COLOR_CYAN . str_repeat('=', 60) . COLOR_END . "\n";
    echo COLOR_BOLD . COLOR_CYAN . str_pad($text, 60, ' ', STR_PAD_BOTH) . COLOR_END . "\n";
    echo COLOR_BOLD . COLOR_CYAN . str_repeat('=', 60) . COLOR_END . "\n\n";
}

function printStep(string $text): void
{
    echo COLOR_BOLD . "🔄 {$text}" . COLOR_END . "\n";
}

function printSuccess(string $text): void
{
    echo COLOR_GREEN . "✅ {$text}" . COLOR_END . "\n";
}

function printWarning(string $text): void
{
    echo COLOR_YELLOW . "⚠️  {$text}" . COLOR_END . "\n";
}

function printError(string $text): void
{
    echo COLOR_RED . "❌ {$text}" . COLOR_END . "\n";
}

/**
 * Run a command and return result
 */
function runCheck(string $name, string $command, bool $required = false): array
{
    printStep("Running: {$name}");
    
    $output = [];
    $returnCode = 0;
    
    exec($command . ' 2>&1', $output, $returnCode);
    
    $passed = $returnCode === 0;
    
    if ($passed) {
        printSuccess("{$name}: PASSED");
    } else {
        printError("{$name}: FAILED");
        // Show first few lines of output on failure
        $preview = array_slice($output, 0, 5);
        foreach ($preview as $line) {
            echo "  {$line}\n";
        }
    }
    
    return [
        'name' => $name,
        'passed' => $passed,
        'output' => implode("\n", $output),
        'required' => $required,
    ];
}

/**
 * Check if a command exists
 */
function commandExists(string $command): bool
{
    $check = PHP_OS_FAMILY === 'Windows' 
        ? "where {$command} 2>nul" 
        : "which {$command} 2>/dev/null";
    
    exec($check, $output, $returnCode);
    return $returnCode === 0;
}

/**
 * Check if file exists in project
 */
function fileExists(string $path): bool
{
    return file_exists($path);
}

/**
 * Print summary
 */
function printSummary(array $results): bool
{
    printHeader("📊 CHECKLIST SUMMARY");
    
    $passed = 0;
    $failed = 0;
    $skipped = 0;
    
    foreach ($results as $r) {
        if ($r['skipped'] ?? false) {
            $skipped++;
        } elseif ($r['passed']) {
            $passed++;
        } else {
            $failed++;
        }
    }
    
    echo "Total Checks: " . count($results) . "\n";
    echo COLOR_GREEN . "✅ Passed: {$passed}" . COLOR_END . "\n";
    echo COLOR_RED . "❌ Failed: {$failed}" . COLOR_END . "\n";
    echo COLOR_YELLOW . "⏭️  Skipped: {$skipped}" . COLOR_END . "\n\n";
    
    foreach ($results as $r) {
        if ($r['skipped'] ?? false) {
            $status = COLOR_YELLOW . "⏭️ " . COLOR_END;
        } elseif ($r['passed']) {
            $status = COLOR_GREEN . "✅" . COLOR_END;
        } else {
            $status = COLOR_RED . "❌" . COLOR_END;
        }
        echo "{$status} {$r['name']}\n";
    }
    
    echo "\n";
    
    if ($failed > 0) {
        printError("{$failed} check(s) FAILED - Please fix before proceeding");
        return false;
    }
    
    printSuccess("All checks PASSED ✨");
    return true;
}

// Main execution
printHeader("🚀 ANTIGRAVITY LARAVEL KIT - CHECKLIST");

$results = [];

// P0: Security - Composer Audit
if (fileExists('composer.lock')) {
    $results[] = runCheck('Security (composer audit)', 'composer audit', true);
} else {
    printWarning("Security: No composer.lock found, skipping");
    $results[] = ['name' => 'Security (composer audit)', 'passed' => true, 'skipped' => true];
}

// P1: Code Style - Pint
if (fileExists('vendor/bin/pint')) {
    $results[] = runCheck('Code Style (Pint)', './vendor/bin/pint --test', true);
} else {
    printWarning("Pint: Not installed, skipping");
    $results[] = ['name' => 'Code Style (Pint)', 'passed' => true, 'skipped' => true];
}

// P2: Static Analysis - PHPStan (optional)
if (fileExists('vendor/bin/phpstan')) {
    $results[] = runCheck('Static Analysis (PHPStan)', './vendor/bin/phpstan analyse --no-progress', false);
} else {
    printWarning("PHPStan: Not installed, skipping");
    $results[] = ['name' => 'Static Analysis (PHPStan)', 'passed' => true, 'skipped' => true];
}

// P3: Tests - Pest or PHPUnit
if (fileExists('vendor/bin/pest')) {
    $results[] = runCheck('Tests (Pest)', 'php artisan test --compact', false);
} elseif (fileExists('vendor/bin/phpunit')) {
    $results[] = runCheck('Tests (PHPUnit)', './vendor/bin/phpunit', false);
} else {
    printWarning("Tests: No test runner found, skipping");
    $results[] = ['name' => 'Tests', 'passed' => true, 'skipped' => true];
}

// P4: Database - Migration status
if (fileExists('artisan')) {
    $results[] = runCheck('Database (Migrations)', 'php artisan migrate:status', false);
}

// Print summary
$allPassed = printSummary($results);

exit($allPassed ? 0 : 1);
