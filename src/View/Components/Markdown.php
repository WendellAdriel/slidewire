<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use WendellAdriel\SlideWire\Support\MarkdownRenderer;
use WendellAdriel\SlideWire\Support\SlideContext;

class Markdown extends Component
{
    public function __construct(
        protected MarkdownRenderer $renderer,
        protected SlideContext $context,
        public ?string $size = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('slidewire::components.markdown');
    }

    public function toHtml(string $markdown): string
    {
        return $this->renderer->toHtml(
            $markdown,
            $this->context->presentationTheme(),
            $this->context->highlightTheme(),
            $this->size,
        );
    }
}
