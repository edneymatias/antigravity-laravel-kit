<?php

namespace Antigravity\LaravelKit\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'antigravity:install 
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Antigravity Laravel Kit agent files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Antigravity Laravel Kit...');

        $filesystem = new Filesystem();
        $sourcePath = dirname(__DIR__, 2) . '/.agent';
        $destinationPath = base_path('.agent');

        // Check if destination exists
        if ($filesystem->isDirectory($destinationPath) && !$this->option('force')) {
            if (!$this->confirm('.agent directory already exists. Do you want to overwrite?')) {
                $this->warn('Installation cancelled.');
                return self::FAILURE;
            }
        }

        // Copy .agent folder
        $filesystem->copyDirectory($sourcePath, $destinationPath);

        $this->info('');
        $this->info('✅ Antigravity Laravel Kit installed successfully!');
        $this->info('');
        $this->info('📁 Created: .agent/');
        $this->info('   ├── agents/       (18 specialist agents)');
        $this->info('   ├── skills/       (32 skills)');
        $this->info('   ├── workflows/    (10 workflows)');
        $this->info('   └── scripts/      (4 PHP scripts)');
        $this->info('');
        $this->info('💡 Usage examples:');
        $this->info('   php .agent/scripts/checklist.php    # Run validation');
        $this->info('   php .agent/scripts/verify_all.php   # Full verification');
        $this->info('');

        return self::SUCCESS;
    }
}
