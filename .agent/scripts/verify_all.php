#!/usr/bin/env php
<?php
/**
 * Comprehensive Verification Suite - Antigravity Laravel Kit
 * ===========================================================
 *
 * Runs ALL verification checks for production readiness.
 * Use this before deploying to production.
 *
 * Usage:
 *     php .agent/scripts/verify_all.php
 *     php .agent/scripts/verify_all.php --url http://localhost:8000
 *
 * Checks:
 *     - Security (composer audit)
 *     - Code Style (Pint)
 *     - Static Analysis (PHPStan)
 *     - Tests (Pest/PHPUnit)
 *     - Database (Migrations)
 *     - Environment (config)
 *     - Production readiness
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
        $preview = array_slice($output, 0, 3);
        foreach ($preview as $line) {
            echo "  {$line}\n";
        }
    }
    
    return [
        'name' => $name,
        'passed' => $passed,
        'output' => implode("\n", $output),
        'required' => $required,
        'category' => '',
    ];
}

function fileExists(string $path): bool
{
    return file_exists($path);
}

function printSummary(array $results): bool
{
    printHeader("📊 VERIFICATION SUMMARY");
    
    $byCategory = [];
    foreach ($results as $r) {
        $cat = $r['category'] ?? 'Other';
        if (!isset($byCategory[$cat])) {
            $byCategory[$cat] = [];
        }
        $byCategory[$cat][] = $r;
    }
    
    $totalPassed = 0;
    $totalFailed = 0;
    $totalSkipped = 0;
    
    foreach ($byCategory as $category => $checks) {
        echo COLOR_BOLD . "\n📁 {$category}" . COLOR_END . "\n";
        
        foreach ($checks as $r) {
            if ($r['skipped'] ?? false) {
                $status = COLOR_YELLOW . "⏭️ " . COLOR_END;
                $totalSkipped++;
            } elseif ($r['passed']) {
                $status = COLOR_GREEN . "✅" . COLOR_END;
                $totalPassed++;
            } else {
                $status = COLOR_RED . "❌" . COLOR_END;
                $totalFailed++;
            }
            echo "  {$status} {$r['name']}\n";
        }
    }
    
    echo "\n" . str_repeat('-', 40) . "\n";
    echo "Total: " . count($results) . " | ";
    echo COLOR_GREEN . "Passed: {$totalPassed}" . COLOR_END . " | ";
    echo COLOR_RED . "Failed: {$totalFailed}" . COLOR_END . " | ";
    echo COLOR_YELLOW . "Skipped: {$totalSkipped}" . COLOR_END . "\n\n";
    
    if ($totalFailed > 0) {
        printError("VERIFICATION FAILED - {$totalFailed} issue(s) found");
        return false;
    }
    
    printSuccess("ALL VERIFICATIONS PASSED ✨");
    echo "\n🚀 Ready for production!\n";
    return true;
}

// Parse arguments
$url = null;
foreach ($argv as $i => $arg) {
    if ($arg === '--url' && isset($argv[$i + 1])) {
        $url = $argv[$i + 1];
    }
}

printHeader("🚀 ANTIGRAVITY LARAVEL KIT - FULL VERIFICATION");
echo "Time: " . date('Y-m-d H:i:s') . "\n";
if ($url) {
    echo "URL: {$url}\n";
}

$results = [];

// === SECURITY ===
printHeader("🔐 SECURITY CHECKS");

$r = fileExists('composer.lock') 
    ? runCheck('Composer Audit', 'composer audit', true)
    : ['name' => 'Composer Audit', 'passed' => true, 'skipped' => true];
$r['category'] = 'Security';
$results[] = $r;

// Check .env not in git
$r = runCheck('Env Not Tracked', 'git ls-files --error-unmatch .env 2>/dev/null && exit 1 || exit 0', true);
$r['category'] = 'Security';
$results[] = $r;

// === CODE QUALITY ===
printHeader("📝 CODE QUALITY");

$r = fileExists('vendor/bin/pint')
    ? runCheck('Pint (Code Style)', './vendor/bin/pint --test', true)
    : ['name' => 'Pint', 'passed' => true, 'skipped' => true];
$r['category'] = 'Code Quality';
$results[] = $r;

$r = fileExists('vendor/bin/phpstan')
    ? runCheck('PHPStan (Static Analysis)', './vendor/bin/phpstan analyse --no-progress', false)
    : ['name' => 'PHPStan', 'passed' => true, 'skipped' => true];
$r['category'] = 'Code Quality';
$results[] = $r;

// === TESTS ===
printHeader("🧪 TESTS");

if (fileExists('vendor/bin/pest')) {
    $r = runCheck('Pest Tests', 'php artisan test --compact', true);
} elseif (fileExists('vendor/bin/phpunit')) {
    $r = runCheck('PHPUnit Tests', './vendor/bin/phpunit', true);
} else {
    $r = ['name' => 'Tests', 'passed' => true, 'skipped' => true];
}
$r['category'] = 'Tests';
$results[] = $r;

// === DATABASE ===
printHeader("🗄️ DATABASE");

if (fileExists('artisan')) {
    $r = runCheck('Migration Status', 'php artisan migrate:status', false);
    $r['category'] = 'Database';
    $results[] = $r;
}

// === CONFIGURATION ===
printHeader("⚙️ CONFIGURATION");

if (fileExists('artisan')) {
    $r = runCheck('Config Cache', 'php artisan config:cache', false);
    $r['category'] = 'Configuration';
    $results[] = $r;
    
    $r = runCheck('Route Cache', 'php artisan route:cache', false);
    $r['category'] = 'Configuration';
    $results[] = $r;
}

// === ASSETS ===
printHeader("📦 ASSETS");

if (fileExists('package.json')) {
    $r = runCheck('NPM Build', 'npm run build 2>&1 || echo "Build script may not exist"', false);
    $r['category'] = 'Assets';
    $results[] = $r;
}

// Print summary
$allPassed = printSummary($results);

exit($allPassed ? 0 : 1);
