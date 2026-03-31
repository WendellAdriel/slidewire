<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use RuntimeException;
use WendellAdriel\SlideWire\DTOs\Slide;

class MarkdownPresentationParser
{
    public function __construct(
        protected MarkdownRenderer $renderer,
        protected SlideIdGenerator $slideIdGenerator,
    ) {}

    /**
     * @return array{deck_meta: array<string, string>, slides: array<int, array<int, Slide>>}
     */
    public function parse(string $path, string $source): array
    {
        $source = str_replace(["\r\n", "\r"], "\n", $source);

        [$deckMeta, $body] = $this->extractFrontmatter($source, $path, 'deck');
        $segments = $this->splitSlides($body);
        $slides = [];

        foreach ($segments as $hIndex => $segment) {
            $slide = $this->parseSlide($path, $segment, $deckMeta, $hIndex);

            if (! $slide instanceof Slide) {
                continue;
            }

            $slides[] = [$slide];
        }

        return ['deck_meta' => $deckMeta, 'slides' => $slides];
    }

    protected function parseSlide(string $path, string $segment, array $deckMeta, int $hIndex): ?Slide
    {
        [$slideMeta, $body] = $this->extractFrontmatter($segment, $path, 'slide');
        $body = trim($body);

        if ($body === '' && $slideMeta === []) {
            return null;
        }

        $presentationTheme = $slideMeta['theme'] ?? $deckMeta['theme'] ?? null;
        $highlightTheme = $slideMeta['highlight_theme'] ?? $deckMeta['highlight_theme'] ?? null;
        $html = trim($this->renderer->toHtml($body, $presentationTheme, $highlightTheme));

        return new Slide(
            id: $this->slideIdGenerator->fromPath($path, $hIndex, 0),
            html: $html,
            meta: $slideMeta,
            fragments: $this->fragmentCount($html),
            h: $hIndex,
            v: 0,
        );
    }

    /**
     * @return array{0: array<string, string>, 1: string}
     */
    protected function extractFrontmatter(string $source, string $path, string $context): array
    {
        $trimmedSource = ltrim($source, "\n");

        if (! str_starts_with($trimmedSource, "---\n") && trim((string) strtok($trimmedSource, "\n")) !== '---') {
            return [[], $source];
        }

        $lines = explode("\n", $trimmedSource);

        if (($lines[0] ?? null) !== '---') {
            throw new RuntimeException("Malformed {$context} frontmatter in markdown presentation [{$path}].");
        }

        $frontmatterLines = [];
        $closingIndex = null;

        foreach ($lines as $index => $line) {
            if ($index === 0) {
                continue;
            }

            if ($line === '---') {
                $closingIndex = $index;

                break;
            }

            $frontmatterLines[] = $line;
        }

        if ($closingIndex === null) {
            throw new RuntimeException("Malformed {$context} frontmatter in markdown presentation [{$path}].");
        }

        $body = implode("\n", array_slice($lines, $closingIndex + 1));

        return [$this->parseFrontmatter($frontmatterLines, $path, $context), ltrim($body, "\n")];
    }

    /**
     * @param  array<int, string>  $lines
     * @return array<string, string>
     */
    protected function parseFrontmatter(array $lines, string $path, string $context): array
    {
        $meta = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            if (preg_match('/^\s+/', $line) === 1 || preg_match('/^\s*-\s*/', $line) === 1) {
                throw new RuntimeException("Unsupported non-scalar {$context} frontmatter value in markdown presentation [{$path}].");
            }

            if (preg_match('/^([A-Za-z0-9_-]+):\s*(.*)$/', $line, $matches) !== 1) {
                throw new RuntimeException("Malformed {$context} frontmatter in markdown presentation [{$path}].");
            }

            $value = trim($matches[2]);

            if ($value !== '' && preg_match('/^[\[{]/', $value) === 1) {
                throw new RuntimeException("Unsupported non-scalar {$context} frontmatter value in markdown presentation [{$path}].");
            }

            if (
                strlen($value) >= 2
                && (($value[0] === '"' && $value[strlen($value) - 1] === '"') || ($value[0] === '\'' && $value[strlen($value) - 1] === '\''))
            ) {
                $value = substr($value, 1, -1);
            }

            $meta[$this->normalizeMetaKey($matches[1])] = $value;
        }

        return $meta;
    }

    /**
     * @return array<int, string>
     */
    protected function splitSlides(string $markdown): array
    {
        if (trim($markdown) === '') {
            return [];
        }

        $segments = [];
        $buffer = [];
        $inFence = false;

        foreach (explode("\n", $markdown) as $line) {
            if ($this->isFenceDelimiter($line)) {
                $inFence = ! $inFence;
            }

            if (! $inFence && trim($line) === '<!-- slide -->') {
                $segments[] = implode("\n", $buffer);
                $buffer = [];

                continue;
            }

            $buffer[] = $line;
        }

        $segments[] = implode("\n", $buffer);

        return $segments;
    }

    protected function isFenceDelimiter(string $line): bool
    {
        return preg_match('/^\s*```/', $line) === 1;
    }

    protected function normalizeMetaKey(string $key): string
    {
        return str_replace('-', '_', strtolower(trim($key)));
    }

    protected function fragmentCount(string $html): int
    {
        preg_match_all('/data-fragment(?:-index)?="?(\d+)?"?/', $html, $matches);

        if ($matches[0] === []) {
            return 0;
        }

        $indices = array_filter($matches[1], static fn (string $value): bool => $value !== '');

        if ($indices === []) {
            return count($matches[0]);
        }

        return max(array_map(intval(...), $indices)) + 1;
    }
}
