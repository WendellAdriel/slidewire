<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use WendellAdriel\SlideWire\DTOs\RemoteConfig;
use WendellAdriel\SlideWire\DTOs\RemoteState;
use WendellAdriel\SlideWire\Support\RemoteSessionManager;

beforeEach(function (): void {
    $this->manager = new RemoteSessionManager();
});

it('parses TTL DSL correctly', function (string $input, int $expected): void {
    expect($this->manager->parseTtl($input))->toBe($expected);
})->with([
    ['30m', 1800],
    ['2h', 7200],
    ['1d', 86400],
    ['90m', 5400],
    ['24h', 86400],
]);

it('throws on invalid TTL format', function (): void {
    $this->manager->parseTtl('abc');
})->throws(InvalidArgumentException::class);

it('rejects a non-positive TTL', function (): void {
    $this->manager->parseTtl('0m');
})->throws(InvalidArgumentException::class);

it('validates a poll interval and returns it', function (): void {
    expect($this->manager->validatePollInterval('750ms'))->toBe('750ms');
});

it('throws on an invalid poll interval', function (): void {
    $this->manager->validatePollInterval('fast');
})->throws(InvalidArgumentException::class);

it('creates a session and returns session data', function (): void {
    $result = $this->manager->create('pitch', '2h');

    expect($result)
        ->toHaveKeys(['key', 'ttl_seconds'])
        ->and($result['key'])->toBeString()->toHaveLength(24)
        ->and($result['ttl_seconds'])->toBe(7200);

    $state = $this->manager->get($result['key']);

    expect($state)
        ->toBeInstanceOf(RemoteState::class)
        ->and($state->presentation)->toBe('pitch')
        ->and($state->index)->toBe(0)
        ->and($state->fragment)->toBe(-1)
        ->and($state->viewerControls)->toBeFalse()
        ->and($state->pollInterval)->toBe('2s');
});

it('uses configured default TTL when none provided', function (): void {
    config(['slidewire.remote' => new RemoteConfig(ttl: '45m')]);

    $result = $this->manager->create('pitch');

    expect($result['ttl_seconds'])->toBe(2700);
});

it('seeds viewer_controls from config', function (): void {
    config(['slidewire.remote' => new RemoteConfig(viewerControls: true)]);

    $result = $this->manager->create('pitch', '1h');

    expect($this->manager->get($result['key'])->viewerControls)->toBeTrue();
});

it('seeds poll_interval from config', function (): void {
    config(['slidewire.remote' => new RemoteConfig(pollInterval: '3s')]);

    $result = $this->manager->create('pitch', '1h');

    expect($this->manager->get($result['key'])->pollInterval)->toBe('3s');
});

it('accepts a custom poll interval', function (): void {
    $result = $this->manager->create('pitch', '1h', '750ms');

    expect($this->manager->get($result['key'])->pollInterval)->toBe('750ms');
});

it('updates session state and preserves remaining ttl', function (): void {
    $result = $this->manager->create('pitch', '1h');

    $this->manager->update($result['key'], 5, 2, false);

    $state = $this->manager->get($result['key']);

    expect($state->index)->toBe(5)
        ->and($state->fragment)->toBe(2)
        ->and($state->viewerControls)->toBeFalse();
});

it('does not resurrect a missing session on update', function (): void {
    $this->manager->update('nonexistent', 1, 1, true);

    expect($this->manager->get('nonexistent'))->toBeNull();
});

it('returns null for a non-existent session', function (): void {
    expect($this->manager->get('nonexistent'))->toBeNull();
});

it('deletes a session', function (): void {
    $result = $this->manager->create('pitch', '1h');

    $this->manager->delete($result['key']);

    expect($this->manager->get($result['key']))->toBeNull();
});

it('checks if a session exists', function (): void {
    $result = $this->manager->create('pitch', '1h');

    expect($this->manager->exists($result['key']))->toBeTrue()
        ->and($this->manager->exists('nonexistent'))->toBeFalse();
});

it('uses the configured cache store when set', function (): void {
    config(['slidewire.remote' => new RemoteConfig(cacheStore: 'array')]);

    $result = $this->manager->create('pitch', '1h');

    expect(Cache::store('array')->has("slidewire:remote:{$result['key']}"))->toBeTrue();
});
