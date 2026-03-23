<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class TimelineSlide extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public ?string $title = null,
        public string $orientation = 'vertical',
        public string|int|null $highlight = null,
        public ?string $theme = null,
    ) {
        $this->orientation = in_array($this->orientation, ['vertical', 'horizontal'], true) ? $this->orientation : 'vertical';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.timeline-slide');
    }

    public function wrapperClass(): string
    {
        return 'mx-auto flex min-h-full w-full max-w-6xl flex-col justify-center gap-8 sm:gap-6';
    }

    public function listClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);
        $layout = $this->orientation === 'horizontal'
            ? 'grid gap-4 md:grid-cols-2 xl:grid-cols-4'
            : 'grid gap-4';

        return trim($layout . ' rounded-[2rem] border p-5 sm:p-6 ' . $tokens['glass_surface'] . ' ' . $tokens['border']);
    }
}
