<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use InvalidArgumentException;
use WendellAdriel\SlideWire\DTOs\RemoteConfig;
use WendellAdriel\SlideWire\DTOs\RemoteState;

class RemoteSessionManager
{
    /**
     * Parse a human-readable TTL string (e.g. '2h', '30m', '1d') into seconds.
     */
    public function parseTtl(string $ttl): int
    {
        if (preg_match(RemoteConfig::TTL_PATTERN, $ttl, $matches) !== 1) {
            throw new InvalidArgumentException("Invalid TTL format: [{$ttl}]. Use formats like '30m', '2h', or '1d'.");
        }

        $multiplier = match ($matches[2]) {
            'm' => 60,
            'h' => 3600,
            'd' => 86400,
        };

        return $this->durationValue($matches[1], intdiv(PHP_INT_MAX - now()->timestamp, $multiplier), 'TTL') * $multiplier;
    }

    /**
     * Validate a poll-interval DSL string (e.g. '500ms', '2s').
     */
    public function validatePollInterval(string $pollInterval): string
    {
        if (preg_match(RemoteConfig::POLL_PATTERN, $pollInterval, $matches) !== 1) {
            throw new InvalidArgumentException("Invalid poll interval [{$pollInterval}]. Use formats like '500ms' or '2s'.");
        }

        // Browser timers accept at most a signed 32-bit millisecond interval.
        $maximum = $matches[2] === 's' ? intdiv(2_147_483_647, 1000) : 2_147_483_647;
        $this->durationValue($matches[1], $maximum, 'poll interval');

        return $pollInterval;
    }

    /**
     * Create a new remote session, seeding the cache with initial state.
     *
     * @return array{key: string, ttl_seconds: int}
     */
    public function create(string $presentation, ?string $ttl = null, ?string $pollInterval = null): array
    {
        $config = $this->config();
        $ttlSeconds = $this->parseTtl($ttl ?? $config->ttl);
        $pollInterval = $this->validatePollInterval($pollInterval ?? $config->pollInterval);
        $key = Str::random(24);

        $this->store()->put($this->cacheKey($key), [
            'presentation' => $presentation,
            'index' => 0,
            'fragment' => -1,
            'viewer_controls' => $config->viewerControls,
            'poll_interval' => $pollInterval,
            'updated_at' => now()->timestamp,
            'expires_at' => now()->timestamp + $ttlSeconds,
        ], $ttlSeconds);

        return ['key' => $key, 'ttl_seconds' => $ttlSeconds];
    }

    /**
     * Update the session state from the controller, preserving the remaining TTL.
     */
    public function update(string $key, int $index, int $fragment, bool $viewerControls): void
    {
        $store = $this->store();
        $cacheKey = $this->cacheKey($key);
        $this->lock($key)->block(3, function () use ($store, $cacheKey, $index, $fragment, $viewerControls): void {
            $state = $store->get($cacheKey);

            if ($state === null || $state['expires_at'] <= now()->timestamp) {
                return;
            }

            $store->put($cacheKey, [
                ...$state,
                'index' => $index,
                'fragment' => $fragment,
                'viewer_controls' => $viewerControls,
                'updated_at' => now()->timestamp,
            ], now()->setTimestamp($state['expires_at']));
        });
    }

    public function get(string $key): ?RemoteState
    {
        $state = $this->store()->get($this->cacheKey($key));

        return $state === null || $state['expires_at'] <= now()->timestamp
            ? null
            : RemoteState::fromArray($state);
    }

    public function delete(string $key): void
    {
        $this->lock($key)->block(3, fn () => $this->store()->forget($this->cacheKey($key)));
    }

    public function exists(string $key): bool
    {
        return $this->get($key) instanceof RemoteState;
    }

    private function durationValue(string $value, int $maximum, string $label): int
    {
        $parsed = filter_var(ltrim($value, '0'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => $maximum],
        ]);

        if ($parsed === false) {
            throw new InvalidArgumentException("Invalid {$label}: [{$value}]. The value must be between 1 and {$maximum}.");
        }

        return $parsed;
    }

    private function lock(string $key): Lock
    {
        $store = $this->store()->getStore();

        if (! $store instanceof LockProvider) {
            throw new InvalidArgumentException('SlideWire remote cache store must support atomic locks.');
        }

        return $store->lock($this->cacheKey($key) . ':lock', 10);
    }

    private function config(): RemoteConfig
    {
        return RemoteConfig::resolved();
    }

    private function cacheKey(string $key): string
    {
        return "slidewire:remote:{$key}";
    }

    private function store(): Repository
    {
        return Cache::store($this->config()->cacheStore);
    }
}
