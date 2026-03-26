<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\DTOs;

use Phiki\Theme\Theme;

final readonly class HighlightConfig
{
    public function __construct(
        public bool $enabled = true,
        public Theme $theme = Theme::CatppuccinMocha,
        public string $font = 'JetBrainsMono',
        public string $fontSize = 'text-base',
    ) {}

    /**
     * @param  array{enabled: bool, theme: Theme, font: string, fontSize: string}  $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self(...$properties);
    }
}
