<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\DTOs;

use Phiki\Theme\Theme;
use Stringable;

final readonly class ThemeConfig implements Stringable
{
    public function __construct(
        public string $background,
        public Theme $highlightTheme,
        public ThemeFont $title,
        public ThemeFont $text,
    ) {}

    /**
     * @param  array{background: string, highlightTheme: Theme, title: ThemeFont, text: ThemeFont}  $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self(...$properties);
    }

    public function __toString(): string
    {
        return $this->background;
    }
}
