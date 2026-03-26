<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\DTOs;

use Stringable;

final readonly class ThemeFont implements Stringable
{
    public function __construct(
        public string $font,
        public string $color,
        public string $size,
    ) {}

    /**
     * @param  array{font: string, color: string, size: string}  $properties
     */
    public static function __set_state(array $properties): self
    {
        return new self(...$properties);
    }

    public function __toString(): string
    {
        return implode(' ', array_filter([$this->font, $this->color, $this->size]));
    }
}
