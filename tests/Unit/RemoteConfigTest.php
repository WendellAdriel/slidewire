<?php

declare(strict_types=1);

use WendellAdriel\SlideWire\DTOs\RemoteConfig;

it('exposes remote defaults', function (): void {
    $config = new RemoteConfig();

    expect($config->ttl)->toBe('2h')
        ->and($config->pollInterval)->toBe('2s')
        ->and($config->viewerControls)->toBeFalse()
        ->and($config->cacheStore)->toBeNull();
});

it('supports var_export hydration for the remote config dto', function (): void {
    $config = new RemoteConfig(ttl: '30m', pollInterval: '750ms', viewerControls: false, cacheStore: 'redis');

    // Verifies the config-cache hydration path (Laravel var_exports config, PHP rehydrates
    // via __set_state). eval() operates only on our own var_export output, never user input.
    // Mirrors the existing DTO round-trip test in ConfigValidatorTest.
    /** @var RemoteConfig $rehydrated */
    $rehydrated = eval('return ' . var_export($config, true) . ';');

    expect($rehydrated)->toEqual($config);
});

it('resolves the configured remote config', function (): void {
    config(['slidewire.remote' => new RemoteConfig(ttl: '5h')]);

    expect(RemoteConfig::resolved()->ttl)->toBe('5h');
});

it('resolves to defaults when the remote config is absent', function (): void {
    config(['slidewire.remote' => null]);

    expect(RemoteConfig::resolved())->toEqual(new RemoteConfig());
});
