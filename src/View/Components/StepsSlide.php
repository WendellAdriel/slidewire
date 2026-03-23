<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class StepsSlide extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public ?string $title = null,
        public string $columns = '3',
        public string $style = 'cards',
        public ?string $theme = null,
    ) {
        $this->columns = in_array($this->columns, ['1', '2', '3'], true) ? $this->columns : '3';
        $this->style = in_array($this->style, ['cards', 'minimal', 'connected'], true) ? $this->style : 'cards';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.steps-slide');
    }

    public function wrapperClass(): string
    {
        return 'mx-auto flex min-h-full w-full max-w-6xl flex-col justify-center gap-8 sm:gap-6';
    }

    public function listClass(): string
    {
        $columns = $this->ui->select($this->columns, [
            '1' => 'grid-cols-1',
            '2' => 'grid-cols-1 lg:grid-cols-2',
            '3' => 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3',
        ], '3');

        $connected = $this->style === 'connected'
            ? 'rounded-[2rem] border p-5 sm:p-6 '
            : '';

        $tokens = $this->ui->tokens($this->theme);

        return trim('grid [counter-reset:step] ' . $columns . ' ' . $this->ui->gap('lg') . ' ' . $connected . ($connected !== '' ? $tokens['glass_surface'] . ' ' . $tokens['border'] : ''));
    }
}
