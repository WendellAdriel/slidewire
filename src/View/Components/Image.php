<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Enums\SlideTransitionSpeed;

class Image extends Component
{
    public function __construct(
        public ?string $animation = null,
        public string $animationSpeed = SlideTransitionSpeed::Default->value,
    ) {
        $this->animationSpeed = $this->normalizeAnimationSpeed($this->animationSpeed);
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.image');
    }

    public function normalizedAnimationSpeed(): string
    {
        return $this->animationSpeed;
    }

    protected function normalizeAnimationSpeed(string $animationSpeed): string
    {
        return in_array($animationSpeed, SlideTransitionSpeed::values(), true)
            ? $animationSpeed
            : SlideTransitionSpeed::Default->value;
    }
}
