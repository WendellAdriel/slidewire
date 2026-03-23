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
        $palette = $this->palette();

        $surface = match ($this->status) {
            'complete', 'upcoming' => $palette['soft'],
            'current' => trim($palette['soft'] . ' ring-1 ring-inset ' . $palette['ring']),
            default => $tokens['muted_surface'],
        };

        return trim('slidewire-timeline-item flex min-w-0 gap-4 rounded-[1.5rem] border p-4 sm:p-5 transition duration-300 ' . $surface . ' ' . $palette['ring']);
    }

    public function markerClass(): string
    {
        $palette = $this->palette();

        return trim('slidewire-timeline-marker mt-0.5 inline-flex size-9 shrink-0 items-center justify-center rounded-full border ' . $palette['ring'] . ' ' . $palette['soft'] . ' ' . $palette['text']);
    }

    public function iconClass(): string
    {
        return 'size-4';
    }

    public function overlineClass(): string
    {
        return trim($this->ui->overline($this->theme, true) . ' ' . $this->palette()['text']);
    }

    public function titleClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);
        $emphasis = $this->status === 'default' ? $tokens['heading_text'] : $this->palette()['text'];

        return trim('slidewire-timeline-title text-xl font-semibold text-balance sm:text-lg ' . $emphasis);
    }

    public function descriptionClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);
        $emphasis = match ($this->status) {
            'current' => $tokens['heading_text'],
            default => $tokens['body_text'],
        };

        return trim('slidewire-timeline-description max-w-none text-pretty text-lg/7 sm:text-base/7 ' . $emphasis);
    }

    /** @return array{ring: string, text: string, soft: string} */
    protected function palette(): array
    {
        return match ($this->status) {
            'complete' => $this->ui->tone('success', $this->theme),
            'current' => $this->ui->tone('primary', $this->theme),
            'upcoming' => $this->ui->tone('warning', $this->theme),
            default => $this->ui->tone(null, $this->theme),
        };
    }
}
