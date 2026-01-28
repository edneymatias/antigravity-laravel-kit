#!/usr/bin/env php
<?php
/**
 * i18n Checker for Laravel
 * ========================
 * Scans Blade templates for potentially hardcoded strings.
 *
 * Usage:
 *     php .agent/skills/i18n-localization/scripts/i18n_checker.php [path]
 */

$path = $argv[1] ?? getcwd();
$issues = [];

echo "🔍 Scanning for hardcoded strings in: $path\n\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    if (strpos($file->getPathname(), 'storage/') !== false) continue;
    if (strpos($file->getPathname(), 'vendor/') !== false) continue;
    
    // Only check blade files for now as they are primary view layer
    if (strpos($file->getFilename(), '.blade.php') === false) continue;

    $content = file_get_contents($file->getPathname());
    $lines = explode("\n", $content);

    foreach ($lines as $i => $line) {
        $trim = trim($line);
        if (empty($trim)) continue;
        
        // Skip comments
        if (strpos($trim, '{{--') !== false) continue;
        if (strpos($trim, '//') === 0) continue;
        
        // Naive check: HTML tags with text content not using {{ __() }} or @lang
        // This is heuristic and will have false positives
        if (preg_match('/>([^<]+)</', $trim, $matches)) {
            $text = trim($matches[1]);
            if (!empty($text) && 
                strpos($text, '{{') === false && 
                strpos($text, '@') === false && 
                !is_numeric($text) &&
                strlen($text) > 3) {
                
                $relPath = str_replace(getcwd() . '/', '', $file->getPathname());
                $issues[] = [
                    'file' => $relPath,
                    'line' => $i + 1,
                    'text' => $text
                ];
            }
        }
    }
}

if (empty($issues)) {
    echo "✅ No obvious hardcoded strings found.\n";
    exit(0);
}

echo "⚠️  Found potentially hardcoded strings:\n";
foreach ($issues as $issue) {
    echo "  • {$issue['file']}:{$issue['line']} -> \"{$issue['text']}\"\n";
}

echo "\nTip: Wrap these in {{ __('string') }} or @lang('string')\n";
exit(1);
