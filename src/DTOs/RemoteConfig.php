<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\DTOs;

final readonly class RemoteConfig
{
    public const string TTL_PATTERN = '/^(\d+)(m|h|d)$/';

    public const string POLL_PATTERN = '/^\d+(ms|s)$/';

    public function __construct(
        public string $ttl = '2h',
        public string $pollInterval = '2s',
        public bool $viewerControls = false,
        public ?string $cacheStore = null,
    ) {}

    /**
     * @param  array{ttl: string, pollInterval: string, viewerControls: bool, cacheStore: string|null}  $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self(...$properties);
    }

    /**
     * Resolve the remote config from the container, falling back to defaults.
     */
    public static function resolved(): self
    {
        $config = config('slidewire.remote');

        return $config instanceof self ? $config : new self();
    }
}
