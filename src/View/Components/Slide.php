<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\DTOs\SlidesConfig;
use WendellAdriel\SlideWire\Support\SlideContext;

class Slide extends Component
{
    public function __construct(
        protected SlideContext $context,
        public ?string $transition = null,
        public ?string $transitionSpeed = null,
        public ?string $background = null,
        public ?string $backgroundImage = null,
        public ?string $backgroundVideo = null,
        public ?string $backgroundVideoLoop = null,
        public ?string $backgroundVideoMuted = null,
        public ?string $backgroundSize = null,
        public ?string $backgroundPosition = null,
        public ?string $backgroundRepeat = null,
        public ?string $backgroundOpacity = null,
        public ?string $backgroundTransition = null,
        public ?string $autoAnimate = null,
        public ?string $autoAnimateDuration = null,
        public ?string $autoAnimateEasing = null,
        public ?string $autoSlide = null,
        public ?string $theme = null,
    ) {
        $this->transition ??= config('slidewire.slides', new SlidesConfig)->transition->value;
        $this->context->setSlide($this->theme);
    }

    public function render(): View|Closure|string
    {
        return function (array $data): string {
            $html = view('slidewire::components.slide', $data)->render();
            $this->context->clearSlide();

            return $html;
        };
    }
}
