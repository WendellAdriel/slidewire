<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\UiThemeResolver;

class SpeakerSlide extends Component
{
    public function __construct(
        public UiThemeResolver $ui,
        public string $name,
        public ?string $role = null,
        public ?string $company = null,
        public ?string $avatar = null,
        public string $align = 'left',
        public string $variant = 'default',
        public ?string $theme = null,
    ) {
        $this->align = in_array($this->align, ['left', 'center'], true) ? $this->align : 'left';
        $this->variant = in_array($this->variant, ['default', 'compact', 'spotlight'], true) ? $this->variant : 'default';
    }

    public function render(): View|Closure|string
    {
        return view('slidewire::components.speaker-slide');
    }

    public function wrapperClass(): string
    {
        $tokens = $this->ui->tokens($this->theme);
        $alignment = $this->align === 'center' ? 'items-center text-center' : 'items-start text-left';
        $layout = $this->variant === 'compact'
            ? 'max-w-4xl gap-5 sm:gap-4'
            : 'max-w-5xl gap-6 sm:gap-5';

        return trim('mx-auto flex min-h-full w-full justify-center rounded-[2rem] border p-6 sm:p-7 ' . $alignment . ' ' . $layout . ' ' . $tokens['glass_surface'] . ' ' . $tokens['border'] . ' ' . $tokens['subtle_glow']);
    }

    public function avatarClass(): string
    {
        $size = match ($this->variant) {
            'compact' => 'size-20 sm:size-16',
            'spotlight' => 'size-36 sm:size-28',
            default => 'size-28 sm:size-24',
        };

        $outline = $this->ui->isLight($this->theme)
            ? 'outline-black/10'
            : 'outline-white/10';

        return trim($size . ' shrink-0 rounded-full object-cover outline-1 -outline-offset-1 ' . $outline);
    }
}
