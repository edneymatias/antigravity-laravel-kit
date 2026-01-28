<?php

namespace Antigravity\LaravelKit;

use Illuminate\Support\ServiceProvider;

class AntigravityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getAgentPath() => base_path('.agent'),
            ], 'agent');

            // Auto-publish on first install (via post-autoload-dump)
            $this->commands([
                Console\InstallCommand::class,
            ]);
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Get the path to the .agent folder in the package.
     */
    protected function getAgentPath(): string
    {
        return dirname(__DIR__) . '/.agent';
    }
}
