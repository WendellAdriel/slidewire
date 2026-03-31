<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use Illuminate\Support\Str;
use Phiki\Theme\Theme;

class MarkdownRenderer
{
    public function __construct(protected CodeHighlighter $highlighter) {}

    public function toHtml(string $markdown, ?string $presentationTheme = null, Theme|string|null $highlightTheme = null, ?string $size = null): string
    {
        $markdown = CodeBlockPrecompiler::decode($markdown);

        $withHighlightedCode = $this->highlighter->replaceCodeBlocks(
            $markdown,
            $highlightTheme,
            $presentationTheme,
            null,
            $size,
        );

        return Str::markdown($withHighlightedCode);
    }
}
