<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

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

        $value = (int) $matches[1];

        if ($value < 1) {
            throw new InvalidArgumentException("Invalid TTL: [{$ttl}]. The value must be at least 1.");
        }

        return match ($matches[2]) {
            'm' => $value * 60,
            'h' => $value * 3600,
            'd' => $value * 86400,
        };
    }

    /**
     * Validate a poll-interval DSL string (e.g. '500ms', '2s').
     */
    public function validatePollInterval(string $pollInterval): string
    {
        if (preg_match(RemoteConfig::POLL_PATTERN, $pollInterval) !== 1) {
            throw new InvalidArgumentException("Invalid poll interval [{$pollInterval}]. Use formats like '500ms' or '2s'.");
        }

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
        $state = $store->get($cacheKey);

        if ($state === null) {
            return;
        }

        $remainingTtl = max(1, $state['expires_at'] - now()->timestamp);

        $store->put($cacheKey, [
            ...$state,
            'index' => $index,
            'fragment' => $fragment,
            'viewer_controls' => $viewerControls,
            'updated_at' => now()->timestamp,
        ], $remainingTtl);
    }

    public function get(string $key): ?RemoteState
    {
        $state = $this->store()->get($this->cacheKey($key));

        return $state === null ? null : RemoteState::fromArray($state);
    }

    public function delete(string $key): void
    {
        $this->store()->forget($this->cacheKey($key));
    }

    public function exists(string $key): bool
    {
        return $this->store()->has($this->cacheKey($key));
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
