<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class Panel extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $variant = 'default',
        public ?string $title = null,
        public ?string $overline = null,
        public ?string $footer = null,
        public string $padding = 'lg',
        public ?string $tone = null,
        public ?string $theme = null,
    ) {
        $this->variant = in_array($this->variant, ['default', 'elevated', 'outlined', 'glass'], true)
            ? $this->variant
            : 'default';

        $this->padding = in_array($this->padding, ['md', 'lg'], true)
            ? $this->padding
            : 'lg';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.panel');
    }

    /** @return array{wrapper: string, header: string, body: string, footer: string, accent_chip: string} */
    public function styles(): array
    {
        return $this->ui->panel($this->variant, $this->tone, $this->theme);
    }

    public function bodyPadding(): string
    {
        return $this->padding === 'md'
            ? 'px-5 py-4 sm:px-6 sm:py-5'
            : 'px-6 py-5 sm:px-7 sm:py-6';
    }

    public function headingClass(): string
    {
        return $this->ui->heading($this->theme);
    }

    public function bodyClass(): string
    {
        return $this->ui->body($this->theme);
    }

    public function overlineClass(): string
    {
        return $this->ui->overline($this->theme);
    }

    public function footerClass(): string
    {
        return $this->ui->meta($this->theme);
    }
}
