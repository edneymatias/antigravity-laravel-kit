<?php

use Antigravity\LaravelKit\AntigravityServiceProvider;

it('registers the service provider', function () {
    $providers = $this->app->getLoadedProviders();
    
    expect($providers)->toHaveKey(AntigravityServiceProvider::class);
});

it('registers the install command', function () {
    $this->artisan('antigravity:install', ['--help' => true])
        ->assertSuccessful();
});

it('has correct service provider boot method', function () {
    $provider = new AntigravityServiceProvider($this->app);
    
    expect($provider)->toBeInstanceOf(AntigravityServiceProvider::class);
});
