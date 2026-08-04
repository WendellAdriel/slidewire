<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use WendellAdriel\SlideWire\DTOs\RemoteConfig;

beforeEach(function (): void {
    Route::slidewire('/slides/pitch', 'pitch');
    // Runtime-registered routes leave the name lookup stale; refreshing it mirrors
    // a real app where route files load through the flow that populates the lookup.
    Route::getRoutes()->refreshNameLookups();
});

it('outputs controller and viewer URLs', function (): void {
    $this->artisan('slidewire:remote', ['presentation' => 'pitch'])
        ->expectsOutputToContain('Controller:')
        ->expectsOutputToContain('Viewer:')
        ->expectsOutputToContain('remote=')
        ->assertExitCode(0);
});

it('generates a signed controller URL', function (): void {
    $this->artisan('slidewire:remote', ['presentation' => 'pitch'])
        ->expectsOutputToContain('signature=')
        ->assertExitCode(0);
});

it('fails when the route does not exist', function (): void {
    $this->artisan('slidewire:remote', ['presentation' => 'nonexistent'])
        ->expectsOutputToContain('No route found')
        ->assertExitCode(1);
});

it('fails with a friendly error on an invalid TTL', function (): void {
    $this->artisan('slidewire:remote', ['presentation' => 'pitch', '--ttl' => 'bogus'])
        ->expectsOutputToContain('Invalid TTL')
        ->assertExitCode(1);
});

it('accepts a custom TTL', function (): void {
    $this->artisan('slidewire:remote', ['presentation' => 'pitch', '--ttl' => '30m'])
        ->expectsOutputToContain('TTL: 30 minutes')
        ->assertExitCode(0);
});

it('accepts a custom poll interval', function (): void {
    $this->artisan('slidewire:remote', ['presentation' => 'pitch', '--poll' => '500ms'])
        ->expectsOutputToContain('Poll interval: 500ms')
        ->assertExitCode(0);
});

it('fails with a friendly error on an invalid poll interval', function (): void {
    $this->artisan('slidewire:remote', ['presentation' => 'pitch', '--poll' => 'bogus'])
        ->expectsOutputToContain('Invalid poll interval')
        ->assertExitCode(1);
});

it('uses the default TTL from config when not specified', function (): void {
    config(['slidewire.remote' => new RemoteConfig(ttl: '1h')]);

    $this->artisan('slidewire:remote', ['presentation' => 'pitch'])
        ->expectsOutputToContain('TTL: 60 minutes')
        ->assertExitCode(0);
});
