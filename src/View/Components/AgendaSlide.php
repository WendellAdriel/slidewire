<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class AgendaSlide extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $title,
        public ?string $subtitle = null,
        public string $style = 'list',
        public string|int|null $highlight = null,
        public ?string $theme = null,
    ) {
        $this->style = in_array($this->style, ['list', 'cards', 'timeline'], true) ? $this->style : 'list';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.agenda-slide');
    }

    public function wrapperClass(): string
    {
        return 'mx-auto flex min-h-full w-full max-w-6xl flex-col justify-center gap-8 sm:gap-6';
    }

    public function listClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);

        return match ($this->style) {
            'cards' => trim('grid gap-4 md:grid-cols-2 rounded-[2rem] border p-5 sm:p-6 ' . $tokens['glass_surface'] . ' ' . $tokens['border']),
            'timeline' => trim('grid gap-4 rounded-[2rem] border p-5 sm:p-6 ' . $tokens['glass_surface'] . ' ' . $tokens['border']),
            default => 'grid gap-3',
        };
    }
}
