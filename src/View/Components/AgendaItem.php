<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class AgendaItem extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $title,
        public ?string $description = null,
        public ?string $index = null,
        public bool $active = false,
        public string $style = 'list',
        public ?string $theme = null,
    ) {
        $this->style = in_array($this->style, ['list', 'cards', 'timeline'], true) ? $this->style : 'list';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.agenda-item');
    }

    public function wrapperClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);
        $palette = $this->active
            ? $this->ui->tone('primary', $this->theme)
            : $this->ui->tone(null, $this->theme);

        return match ($this->style) {
            'cards' => trim('flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 shadow-[0_18px_50px_rgba(15,23,42,0.10)] ' . ($this->active ? $palette['soft'] : $tokens['muted_surface']) . ' ' . $palette['ring']),
            'timeline' => trim('relative flex min-w-0 gap-4 rounded-r-[1.5rem] pl-4 pr-4 py-4 sm:pr-5 before:absolute before:bottom-[-0.95rem] before:left-[1.25rem] before:top-[-0.95rem] before:w-px before:bg-current/20 ' . ($this->active ? $palette['soft'] . ' shadow-[0_16px_45px_rgba(15,23,42,0.08)]' : 'bg-transparent') . ' ' . $palette['text']),
            default => trim('relative flex min-w-0 gap-4 border-b pb-4 pt-1 last:border-b-0 last:pb-0 ' . ($this->active ? 'before:absolute before:inset-y-1 before:left-0 before:w-1 before:rounded-full before:' . $palette['soft'] : '') . ' ' . $palette['ring']),
        };
    }

    public function indexClass(): string
    {
        $palette = $this->active
            ? $this->ui->tone('primary', $this->theme)
            : $this->ui->tone(null, $this->theme);

        return match ($this->style) {
            'timeline' => trim('relative z-10 inline-flex size-10 shrink-0 items-center justify-center rounded-full border-2 bg-white text-sm/6 font-semibold shadow-[0_0_0_6px_rgba(255,255,255,0.9)] ' . $palette['text'] . ' ' . $palette['ring']),
            'cards' => trim('inline-flex size-10 shrink-0 items-center justify-center rounded-full text-sm/6 font-semibold ' . $palette['soft'] . ' ' . $palette['text'] . ' ' . $palette['ring']),
            default => trim('inline-flex size-9 shrink-0 items-center justify-center rounded-full border text-xs/6 font-semibold ' . ($this->active ? $palette['soft'] . ' ' : '') . $palette['text'] . ' ' . $palette['ring']),
        };
    }

    public function contentClass(): string
    {
        return match ($this->style) {
            'list' => 'min-w-0 space-y-1',
            default => 'min-w-0 space-y-2',
        };
    }

    public function titleClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);

        return match ($this->style) {
            'list' => trim($tokens['heading_text'] . ' text-lg font-medium text-balance sm:text-base'),
            default => trim($tokens['heading_text'] . ' text-xl font-semibold text-balance sm:text-lg'),
        };
    }

    public function descriptionClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);

        return match ($this->style) {
            'list' => trim('max-w-none text-sm/6 sm:text-[0.8125rem]/6 ' . $tokens['muted_text']),
            default => $this->ui->body($this->theme) . ' max-w-none',
        };
    }
}
