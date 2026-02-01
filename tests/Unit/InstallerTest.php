<?php

use Antigravity\LaravelKit\Installer;

it('has correct package path', function () {
    $reflection = new ReflectionClass(Installer::class);
    $packagePath = dirname($reflection->getFileName(), 2);
    
    expect($packagePath)->toBeDirectory();
    expect($packagePath . '/.agent')->toBeDirectory();
});

it('has agent directory with required structure', function () {
    $reflection = new ReflectionClass(Installer::class);
    $agentPath = dirname($reflection->getFileName(), 2) . '/.agent';
    
    expect($agentPath . '/agents')->toBeDirectory();
    expect($agentPath . '/skills')->toBeDirectory();
    expect($agentPath . '/workflows')->toBeDirectory();
});

it('has GEMINI.md file', function () {
    $reflection = new ReflectionClass(Installer::class);
    $packagePath = dirname($reflection->getFileName(), 2);
    
    expect($packagePath . '/.agent/rules/GEMINI.md')->toBeFile();
});
