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
            'cards' => trim('flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 ' . ($this->active ? $palette['soft'] : $tokens['muted_surface']) . ' ' . $palette['ring']),
            'timeline' => trim('flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 ' . ($this->active ? $palette['soft'] : 'bg-transparent') . ' ' . $palette['ring']),
            default => trim('flex min-w-0 gap-4 rounded-[1.5rem] border px-4 py-4 sm:px-5 ' . ($this->active ? $palette['soft'] : $tokens['muted_surface']) . ' ' . $palette['ring']),
        };
    }

    public function indexClass(): string
    {
        $palette = $this->active
            ? $this->ui->tone('primary', $this->theme)
            : $this->ui->tone(null, $this->theme);

        return trim('inline-flex size-10 shrink-0 items-center justify-center rounded-full text-sm/6 font-semibold ' . $palette['soft'] . ' ' . $palette['text'] . ' ' . $palette['ring']);
    }
}
