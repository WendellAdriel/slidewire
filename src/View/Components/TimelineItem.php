<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class TimelineItem extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $title,
        public ?string $label = null,
        public ?string $description = null,
        public string $status = 'default',
        public string|int|null $itemKey = null,
        public ?string $theme = null,
    ) {
        $this->status = in_array($this->status, ['default', 'complete', 'current', 'upcoming'], true)
            ? $this->status
            : 'default';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.timeline-item');
    }

    public function wrapperClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);
        $palette = match ($this->status) {
            'complete' => $this->ui->tone('success', $this->theme),
            'current' => $this->ui->tone('primary', $this->theme),
            'upcoming' => $this->ui->tone('warning', $this->theme),
            default => $this->ui->tone(null, $this->theme),
        };

        $surface = $this->status === 'current' ? $palette['soft'] : $tokens['muted_surface'];

        return trim('flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 ' . $surface . ' ' . $palette['ring']);
    }

    public function markerClass(): string
    {
        $palette = match ($this->status) {
            'complete' => $this->ui->tone('success', $this->theme),
            'current' => $this->ui->tone('primary', $this->theme),
            'upcoming' => $this->ui->tone('warning', $this->theme),
            default => $this->ui->tone(null, $this->theme),
        };

        return trim('mt-1 size-3 shrink-0 rounded-full border ' . $palette['ring'] . ' ' . $palette['soft']);
    }
}
