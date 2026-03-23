<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

/**
 * @deprecated Use TwoColumnSlide and wrap the media side with Panel when needed.
 */
class MediaSplitSlide extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $mediaPosition = 'left',
        public string $ratio = '1:1',
        public string $mediaStyle = 'plain',
        public string $gap = 'lg',
        public ?string $theme = null,
    ) {
        $this->mediaPosition = in_array($this->mediaPosition, ['left', 'right'], true) ? $this->mediaPosition : 'left';
        $this->ratio = in_array($this->ratio, ['1:1', '3:2', '2:3'], true) ? $this->ratio : '1:1';
        $this->mediaStyle = in_array($this->mediaStyle, ['plain', 'framed', 'panel'], true) ? $this->mediaStyle : 'plain';
        $this->gap = in_array($this->gap, ['lg', 'xl'], true) ? $this->gap : 'lg';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.media-split-slide');
    }

    public function wrapperClass(): string
    {
        $ratio = $this->ui->select($this->ratio, [
            '1:1' => 'lg:grid-cols-2',
            '3:2' => 'lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]',
            '2:3' => 'lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]',
        ], '1:1');

        return trim('mx-auto grid min-h-full w-full max-w-6xl items-center grid-cols-1 ' . $ratio . ' ' . $this->ui->gap($this->gap));
    }

    public function mediaClass(): string
    {
        return trim('min-w-0 ' . ($this->mediaPosition === 'right' ? 'lg:order-2' : ''));
    }

    public function contentClass(): string
    {
        return trim('min-w-0 ' . ($this->mediaPosition === 'right' ? 'lg:order-1' : ''));
    }

    public function mediaWrapperClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);

        return match ($this->mediaStyle) {
            'framed' => trim('overflow-hidden rounded-[2rem] border p-3 sm:p-4 ' . $tokens['border'] . ' ' . $tokens['muted_surface']),
            'panel' => trim('overflow-hidden rounded-[2rem] border p-4 sm:p-5 ' . $tokens['glass_surface'] . ' ' . $tokens['border'] . ' ' . $tokens['subtle_glow']),
            default => '',
        };
    }
}
