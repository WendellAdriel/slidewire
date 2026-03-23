<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class TitleSlide extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $title,
        public ?string $subtitle = null,
        public ?string $overline = null,
        public ?string $speaker = null,
        public ?string $event = null,
        public ?string $date = null,
        public string $align = 'left',
        public string $variant = 'default',
        public ?string $theme = null,
    ) {
        $this->align = in_array($this->align, ['left', 'center'], true) ? $this->align : 'left';
        $this->variant = in_array($this->variant, ['default', 'hero', 'minimal'], true) ? $this->variant : 'default';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.title-slide');
    }

    public function wrapperClass(): string
    {
        $alignment = $this->align === 'center'
            ? 'items-center text-center'
            : 'items-start text-left';

        $spacing = match ($this->variant) {
            'hero' => 'min-h-full justify-center gap-8 sm:gap-7',
            'minimal' => 'min-h-full justify-center gap-5 sm:gap-4',
            default => 'min-h-full justify-center gap-6 sm:gap-5',
        };

        return trim('mx-auto flex w-full max-w-5xl flex-col ' . $alignment . ' ' . $spacing);
    }

    public function headingClass(): string
    {
        $size = match ($this->variant) {
            'hero' => 'text-5xl sm:text-4xl lg:text-6xl',
            'minimal' => 'text-4xl sm:text-3xl lg:text-5xl',
            default => 'text-[2.75rem] sm:text-4xl lg:text-5xl',
        };

        return trim($this->ui->heading($this->theme) . ' ' . $size . ($this->align === 'center' ? ' mx-auto' : ''));
    }

    public function bodyClass(): string
    {
        return trim($this->ui->body($this->theme) . ($this->align === 'center' ? ' mx-auto' : ''));
    }

    public function overlineClass(): string
    {
        return trim($this->ui->overline($this->theme, true) . ($this->align === 'center' ? ' mx-auto' : ''));
    }

    public function metaClass(): string
    {
        return trim('flex flex-wrap gap-3 text-sm/6 sm:text-[0.8125rem]/6 ' . $this->ui->tokens($this->theme)['muted_text'] . ($this->align === 'center' ? ' justify-center' : ''));
    }
}
