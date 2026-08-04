<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\DTOs;

final readonly class RemoteState
{
    public function __construct(
        public string $presentation,
        public int $index,
        public int $fragment,
        public bool $viewerControls,
        public string $pollInterval,
        public int $updatedAt,
        public int $expiresAt,
    ) {}

    /**
     * @param  array{presentation: string, index: int, fragment: int, viewer_controls: bool, poll_interval: string, updated_at: int, expires_at: int}  $state
     */
    public static function fromArray(array $state): self
    {
        return new self(
            presentation: $state['presentation'],
            index: $state['index'],
            fragment: $state['fragment'],
            viewerControls: $state['viewer_controls'],
            pollInterval: $state['poll_interval'],
            updatedAt: $state['updated_at'],
            expiresAt: $state['expires_at'],
        );
    }
}
