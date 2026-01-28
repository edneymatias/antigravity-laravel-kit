#!/usr/bin/env php
<?php
/**
 * i18n Checker for Laravel (Enhanced)
 * ===================================
 * 1. Scans Blade templates for hardcoded strings
 * 2. Scans PHP classes (Filament resources) for hardcoded labels/titles
 * 3. Checks consistency between locale files (e.g. en.json vs pt.json)
 *
 * Usage:
 *     php .agent/skills/i18n-localization/scripts/i18n_checker.php [path]
 */

$projectPath = $argv[1] ?? getcwd();
$issues = [];
$stats = ['scanned' => 0, 'hardcoded' => 0];

echo "\n" . str_repeat('=', 60) . "\n";
echo "  🌍 Laravel i18n Auditor\n";
echo str_repeat('=', 60) . "\n\n";

// ==============================================================================
// 1. LOCALE CONSISTENCY CHECK
// ==============================================================================
echo "📊 Checking Locale Consistency...\n";

$langPath = $projectPath . '/lang';
if (!is_dir($langPath)) {
    // Laravel 9/10 moved lang to root, older versions in resources/lang
    $langPath = $projectPath . '/resources/lang';
}

if (is_dir($langPath)) {
    $jsonFiles = glob($langPath . '/*.json');
    $phpDirs = glob($langPath . '/*', GLOB_ONLYDIR);
    
    $locales = [];
    
    // Load JSON locales
    foreach ($jsonFiles as $file) {
        $locale = basename($file, '.json');
        $content = json_decode(file_get_contents($file), true);
        if ($content) {
            $locales[$locale]['json'] = array_keys($content);
        }
    }
    
    // Load PHP locales (rough check for top-level keys or dot notation)
    foreach ($phpDirs as $dir) {
        $locale = basename($dir);
        if ($locale === 'vendor') continue;
        
        $files = glob($dir . '/*.php');
        foreach ($files as $file) {
            $namespace = basename($file, '.php');
            $keys = array_keys(include $file);
            // Store as namespace.key
            foreach ($keys as $k) {
                $locales[$locale]['php'][] = "$namespace.$k";
            }
        }
    }
    
    if (count($locales) > 1) {
        $baseLocale = isset($locales['en']) ? 'en' : array_key_first($locales);
        $baseJsonKeys = $locales[$baseLocale]['json'] ?? [];
        $basePhpKeys = $locales[$baseLocale]['php'] ?? [];
        
        foreach ($locales as $probingLocale => $data) {
            if ($probingLocale === $baseLocale) continue;
            
            // Check JSON
            $probingJson = $data['json'] ?? [];
            $missingJson = array_diff($baseJsonKeys, $probingJson);
            if (count($missingJson) > 0) {
                echo "  ⚠️  [{$probingLocale}] Missing " . count($missingJson) . " JSON keys (vs {$baseLocale})\n";
                // Show first 3 missing
                $preview = array_slice($missingJson, 0, 3);
                foreach ($preview as $k) echo "      - $k\n";
            }
            
            // Check PHP
            $probingPhp = $data['php'] ?? [];
            $missingPhp = array_diff($basePhpKeys, $probingPhp);
            if (count($missingPhp) > 0) {
                echo "  ⚠️  [{$probingLocale}] Missing " . count($missingPhp) . " PHP keys (vs {$baseLocale})\n";
            }
        }
        echo "  ✅ Locale structure analysis complete.\n";
    } else {
        echo "  ℹ️  Only one locale found (" . implode(', ', array_keys($locales)) . "). skipping comparison.\n";
    }
} else {
    echo "  ℹ️  No lang directory found. Skipping consistency check.\n";
}

echo "\n";

// ==============================================================================
// 2. HARDCODED STRING SCANNER
// ==============================================================================
echo "🔍 Scanning Codebase for Hardcoded Strings...\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectPath)
);

foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    if ($file->getExtension() !== 'php') continue;
    
    $path = $file->getPathname();
    
    // Exclusions
    if (strpos($path, '/storage/') !== false) continue;
    if (strpos($path, '/vendor/') !== false) continue;
    if (strpos($path, '/tests/') !== false) continue;
    if (strpos($path, '/cache/') !== false) continue;
    if (strpos($path, 'Start.php') !== false) continue; // Tends to parse weirdly
    
    $content = file_get_contents($path);
    $relPath = str_replace($projectPath . '/', '', $path);
    $stats['scanned']++;
    
    $fileIssues = [];
    
    // --------------------------------------------------------------------------
    // A. FILAMENT / FLUENT API CHECK (Classes)
    // --------------------------------------------------------------------------
    if (strpos($path, 'app/Filament') !== false || strpos($content, 'Filament') !== false) {
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            $trim = trim($line);
            if (strpos($trim, '//') === 0) continue;
            
            // Pattern: ->method('String literal')
            // Methods typical of Filament: label, title, placeholder, description, heading, modalHeading
            if (preg_match('/->(label|title|placeholder|description|heading|modalHeading)\s*\(\s*[\'"]([A-Z][^\'"]{2,})[\'"]\s*\)/', $trim, $matches)) {
                
                // Exclude if it looks like a translation key (no spaces, pure lowercase, dots)
                $text = $matches[2];
                if (preg_match('/^[a-z0-9_.-]+$/', $text)) continue; 
                
                $fileIssues[] = [
                    'line' => $i + 1,
                    'text' => $text,
                    'context' => "Filament method ->{$matches[1]}()"
                ];
            }
            
            // Pattern: ::make('String literal')
            // Common in Filament (TextColumn::make, TextInput::make) - often key is used as label
            if (preg_match('/::make\s*\(\s*[\'"]([A-Z][^\'"]{2,})[\'"]\s*\)/', $trim, $matches)) {
                 $fileIssues[] = [
                    'line' => $i + 1,
                    'text' => $matches[1],
                    'context' => "Static ::make()"
                ];
            }
        }
    }
    
    // --------------------------------------------------------------------------
    // B. BLADE TEMPLATE CHECK
    // --------------------------------------------------------------------------
    if (strpos($file->getFilename(), '.blade.php') !== false) {
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            $trim = trim($line);
            if (empty($trim)) continue;
            if (strpos($trim, '{{--') !== false) continue;
            
            // Heuristic: HTML tag content >Text<
            if (preg_match('/>\s*([A-Z][a-zA-Z0-9\s:!?,.-]{3,})\s*</', $trim, $matches)) {
                $text = trim($matches[1]);
                
                // Skip if looks like code or variable
                if (strpos($text, '$') !== false) continue;
                if (strpos($text, '{') !== false) continue;
                if (strpos($text, '@') !== false) continue;
                
                $fileIssues[] = [
                    'line' => $i + 1,
                    'text' => $text,
                    'context' => "Blade HTML content"
                ];
            }
            
            // Heuristic: placeholder="Text" or title="Text" attributes
            if (preg_match('/(placeholder|title|aria-label)=[\'"]([A-Z][a-zA-Z0-9\s]{3,})[\'"]/', $trim, $matches)) {
                $fileIssues[] = [
                    'line' => $i + 1,
                    'text' => $matches[2],
                    'context' => "HTML attribute {$matches[1]}"
                ];
            }
        }
    }
    
    if (!empty($fileIssues)) {
        $stats['hardcoded']++;
        echo "  📄 {$relPath}\n";
        foreach ($fileIssues as $issue) {
            echo "     L{$issue['line']}: \"{$issue['text']}\" ({$issue['context']})\n";
        }
    }
}

echo "\n" . str_repeat('-', 60) . "\n";
echo "Done. Scanned {$stats['scanned']} files.\n";

if ($stats['hardcoded'] > 0) {
    echo "⚠️  Found suspicious strings in {$stats['hardcoded']} files.\n";
    echo "   Tip: Use {{ __('string') }} or ->label(__('string'))\n";
    exit(1);
} else {
    echo "✅ checks passed.\n";
    exit(0);
}
