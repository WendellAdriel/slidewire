<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class StepItem extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $title,
        public ?string $number = null,
        public ?string $description = null,
        public string $style = 'cards',
        public ?string $theme = null,
    ) {
        $this->style = in_array($this->style, ['cards', 'minimal', 'connected'], true) ? $this->style : 'cards';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.step-item');
    }

    public function wrapperClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);

        return match ($this->style) {
            'minimal' => 'flex min-w-0 gap-4',
            'connected' => trim('flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 ' . $tokens['muted_surface'] . ' ' . $tokens['border']),
            default => trim('flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 ' . $tokens['glass_surface'] . ' ' . $tokens['border'] . ' ' . $tokens['subtle_glow']),
        };
    }

    public function badgeClass(): string
    {
        $palette = $this->ui->tone('primary', $this->theme);

        return trim('relative inline-flex size-11 shrink-0 items-center justify-center rounded-full text-sm/6 font-semibold ' . $palette['soft'] . ' ' . $palette['text'] . ' ' . $palette['ring']);
    }
}
