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
            'minimal' => trim('flex min-w-0 items-start gap-3 border-l pl-4 sm:pl-3 ' . $tokens['border']),
            'connected' => trim('flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 ' . $tokens['muted_surface'] . ' ' . $tokens['border']),
            default => trim('flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 ' . $tokens['glass_surface'] . ' ' . $tokens['border'] . ' ' . $tokens['subtle_glow']),
        };
    }

    public function badgeClass(): string
    {
        $palette = $this->ui->tone('primary', $this->theme);

        return match ($this->style) {
            'minimal' => trim('relative inline-flex size-7 shrink-0 items-center justify-center rounded-md border text-[0.7rem]/6 font-medium tabular-nums ' . $palette['text'] . ' ' . $palette['ring']),
            default => trim('relative inline-flex size-11 shrink-0 items-center justify-center rounded-full text-sm/6 font-semibold ' . $palette['soft'] . ' ' . $palette['text'] . ' ' . $palette['ring']),
        };
    }

    public function contentClass(): string
    {
        return match ($this->style) {
            'minimal' => 'min-w-0 space-y-1.5 pt-0.5',
            default => 'min-w-0 space-y-2',
        };
    }

    public function titleClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);

        return match ($this->style) {
            'minimal' => trim($tokens['heading_text'] . ' text-base font-medium tracking-[0.02em] text-balance sm:text-sm'),
            default => trim($tokens['heading_text'] . ' text-xl font-semibold text-balance sm:text-lg'),
        };
    }

    public function descriptionClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);

        return match ($this->style) {
            'minimal' => trim('max-w-none text-sm/6 sm:text-[0.8125rem]/6 ' . $tokens['muted_text']),
            default => $this->ui->body($this->theme) . ' max-w-none',
        };
    }
}
