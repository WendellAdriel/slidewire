<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class TwoColumnSlide extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $ratio = '1:1',
        public string $gap = 'lg',
        public string $align = 'start',
        public bool $reverse = false,
    ) {
        $this->ratio = in_array($this->ratio, ['1:1', '2:1', '1:2'], true) ? $this->ratio : '1:1';
        $this->gap = in_array($this->gap, ['md', 'lg', 'xl'], true) ? $this->gap : 'lg';
        $this->align = in_array($this->align, ['start', 'center', 'stretch'], true) ? $this->align : 'start';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.two-column-slide');
    }

    public function wrapperClass(): string
    {
        $ratio = $this->ui->select($this->ratio, [
            '1:1' => 'lg:grid-cols-2',
            '2:1' => 'lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]',
            '1:2' => 'lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]',
        ], '1:1');

        $alignment = $this->ui->select($this->align, [
            'start' => 'items-start',
            'center' => 'items-center',
            'stretch' => 'items-stretch',
        ], 'start');

        return trim('mx-auto grid min-h-full w-full max-w-6xl grid-cols-1 ' . $ratio . ' ' . $alignment . ' ' . $this->ui->gap($this->gap));
    }

    public function leftClass(): string
    {
        return trim('min-w-0 ' . ($this->reverse ? 'lg:order-2' : ''));
    }

    public function rightClass(): string
    {
        return trim('min-w-0 ' . ($this->reverse ? 'lg:order-1' : ''));
    }
}
