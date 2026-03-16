<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Enums\SlideTransitionSpeed;

class Text extends Component
{
    public function __construct(
        public string $type = 'paragraph',
        public string $orientation = 'horizontal',
        public ?string $animation = null,
        public string $animationSpeed = SlideTransitionSpeed::Default->value,
    ) {
        $this->type = $this->normalizeType($this->type);
        $this->orientation = $this->normalizeOrientation($this->orientation);
        $this->animationSpeed = $this->normalizeAnimationSpeed($this->animationSpeed);
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.text');
    }

    public function tag(): string
    {
        return match ($this->type) {
            'inline' => 'span',
            'heading' => 'h2',
            default => 'p',
        };
    }

    public function normalizedOrientation(): string
    {
        return $this->orientation;
    }

    public function normalizedAnimationSpeed(): string
    {
        return $this->animationSpeed;
    }

    protected function normalizeType(string $type): string
    {
        return in_array($type, ['paragraph', 'inline', 'heading'], true)
            ? $type
            : 'paragraph';
    }

    protected function normalizeOrientation(string $orientation): string
    {
        return in_array($orientation, ['horizontal', 'vertical'], true)
            ? $orientation
            : 'horizontal';
    }

    protected function normalizeAnimationSpeed(string $animationSpeed): string
    {
        return in_array($animationSpeed, SlideTransitionSpeed::values(), true)
            ? $animationSpeed
            : SlideTransitionSpeed::Default->value;
    }
}
