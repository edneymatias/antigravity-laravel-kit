<?php

namespace Antigravity\LaravelKit;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Installer
{
    /**
     * Handle the post-install-cmd event.
     */
    public static function postInstall(Event $event): void
    {
        self::publishAgentFiles($event);
    }

    /**
     * Handle the post-update-cmd event.
     */
    public static function postUpdate(Event $event): void
    {
        // Don't auto-publish on update to avoid overwriting user changes
    }

    /**
     * Publish the .agent folder to the project root.
     */
    protected static function publishAgentFiles(Event $event): void
    {
        $io = $event->getIO();
        
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $projectDir = dirname($vendorDir);
        
        $sourcePath = $vendorDir . '/edneymatias/antigravity-laravel-kit/.agent';
        $destinationPath = $projectDir . '/.agent';

        // Only install if .agent doesn't exist (first install)
        if (is_dir($destinationPath)) {
            $io->write('<info>Antigravity: .agent folder already exists, skipping auto-install.</info>');
            $io->write('<comment>Run "php artisan antigravity:install --force" to reinstall.</comment>');
            return;
        }

        if (!is_dir($sourcePath)) {
            $io->write('<warning>Antigravity: Source .agent folder not found.</warning>');
            return;
        }

        // Copy directory
        self::copyDirectory($sourcePath, $destinationPath);

        $io->write('');
        $io->write('<info>✅ Antigravity Laravel Kit installed!</info>');
        $io->write('<info>📁 Created: .agent/ folder with agents, skills, and workflows</info>');
        $io->write('');
    }

    /**
     * Recursively copy a directory.
     */
    protected static function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
            
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $destPath);
            }
        }
    }
}
