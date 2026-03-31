<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;
use Livewire\Drawer\BaseUtils as LivewireBaseUtils;
use Livewire\Drawer\Utils as LivewireUtils;
use ReflectionMethod;
use RuntimeException;
use SplFileInfo;
use Throwable;
use WendellAdriel\SlideWire\DTOs\Slide;

class PresentationCompiler
{
    public function __construct(
        protected PresentationPathResolver $resolver,
        protected MarkdownPresentationParser $markdownParser,
        protected SlideIdGenerator $slideIdGenerator,
    ) {}

    /**
     * @return array{deck_meta: array<string, string>, slides: array<int, array<int, Slide>>}
     *
     * @throws RuntimeException when the presentation file cannot be read or rendered
     */
    public function compile(string $presentation): array
    {
        $path = $this->resolver->presentationPath($presentation);

        if ($path === null) {
            return ['deck_meta' => [], 'slides' => []];
        }

        return $this->compilePath($path);
    }

    /**
     * @return array{deck_meta: array<string, string>, slides: array<int, array<int, Slide>>}
     */
    public function compilePath(string $path, bool $allowDeckWrapper = true): array
    {
        if (File::isDirectory($path)) {
            return $this->compileDirectory($path);
        }

        return $this->compileFile($path, $allowDeckWrapper);
    }

    /**
     * @param  array<int, array<int, Slide>>  $columns
     * @return array<int, Slide>
     */
    public function flattenSlides(array $columns): array
    {
        $flat = [];

        foreach ($columns as $column) {
            foreach ($column as $slide) {
                $flat[] = $slide;
            }
        }

        return $flat;
    }

    /**
     * @return array{deck_meta: array<string, string>, slides: array<int, array<int, Slide>>}
     *
     * @throws RuntimeException when the file cannot be read or rendered
     */
    protected function compileFile(string $path, bool $allowDeckWrapper = true): array
    {
        if (! File::exists($path)) {
            throw new RuntimeException("Presentation file not found: {$path}");
        }

        try {
            $content = File::get($path);
        } catch (Throwable $e) {
            throw new RuntimeException("Failed to read presentation file [{$path}]: {$e->getMessage()}", $e->getCode(), previous: $e);
        }

        if (str_ends_with($path, '.md')) {
            return $this->markdownParser->parse($path, $content);
        }

        try {
            $html = $this->renderPresentation($path, $content);
        } catch (Throwable $e) {
            throw new RuntimeException("Failed to render presentation [{$path}]: {$e->getMessage()}", $e->getCode(), previous: $e);
        }

        $deckMeta = $this->extractDeckMeta($html);

        $deckInner = $this->extractDeckInner($html);

        if (! $allowDeckWrapper && $deckInner !== null) {
            throw new RuntimeException("Composed presentation part [{$path}] cannot contain a deck wrapper.");
        }

        if ($deckInner === null) {
            return ['deck_meta' => $deckMeta, 'slides' => $this->parseFlatSlides($html, $path)];
        }

        return ['deck_meta' => $deckMeta, 'slides' => $this->parseStructuredSlides($deckInner, $path)];
    }

    protected function renderPresentation(string $path, string $content): string
    {
        if (! $this->isLivewireSingleFileComponent($content)) {
            return Blade::render($content, [], deleteCachedView: true);
        }

        $component = $this->resolveSingleFileComponent($path);
        $this->callMountIfPossible($component);

        $properties = LivewireBaseUtils::getPublicPropertiesDefinedOnSubclass($component);
        $view = LivewireUtils::generateBladeView($this->resolveComponentView($component), $properties);

        if (method_exists($component, 'with')) {
            $withData = $component->with();

            if (is_array($withData)) {
                $view->with($withData);
            }
        }

        return $view->render();
    }

    /**
     * @return array{deck_meta: array<string, string>, slides: array<int, array<int, Slide>>}
     */
    protected function compileDirectory(string $path): array
    {
        $deckMeta = [];
        $deckPath = $path . DIRECTORY_SEPARATOR . 'deck.blade.php';

        if (File::exists($deckPath)) {
            $deckMeta = $this->compileFile($deckPath)['deck_meta'];
        }

        $partPaths = collect(File::files($path))
            ->map(static fn (SplFileInfo $file): string => $file->getPathname())
            ->filter(fn (string $filePath): bool => $this->isComposablePart($filePath))
            ->sort()
            ->values()
            ->all();

        if ($partPaths === []) {
            return ['deck_meta' => $deckMeta, 'slides' => []];
        }

        $columns = [];
        $hIndex = 0;

        foreach ($partPaths as $partPath) {
            $compiled = $this->compileFile($partPath, allowDeckWrapper: false);

            foreach ($this->flattenSlides($compiled['slides']) as $slide) {
                $columns[] = [$slide->withCoordinates($hIndex, 0)];
                ++$hIndex;
            }
        }

        return ['deck_meta' => $deckMeta, 'slides' => $columns];
    }

    protected function isLivewireSingleFileComponent(string $content): bool
    {
        return preg_match('/<\?php\s+.*new\s+class(?:\(\))?\s+extends\s+Component/s', $content) === 1;
    }

    protected function resolveSingleFileComponent(string $path): LivewireComponent
    {
        $className = app('livewire.compiler')->compile($path);
        $component = app($className);

        if (! $component instanceof LivewireComponent) {
            throw new RuntimeException("Compiled presentation [{$path}] is not a Livewire component.");
        }

        return $component;
    }

    protected function callMountIfPossible(LivewireComponent $component): void
    {
        if (! method_exists($component, 'mount')) {
            return;
        }

        $method = new ReflectionMethod($component, 'mount');

        foreach ($method->getParameters() as $parameter) {
            if (! $parameter->isOptional()) {
                return;
            }
        }

        $method->invoke($component);
    }

    protected function resolveComponentView(LivewireComponent $component): ViewContract|string
    {
        if (method_exists($component, 'render')) {
            return $component->render();
        }

        if ($component->hasProvidedView()) {
            return $component->getProvidedView();
        }

        throw new RuntimeException('Livewire SFC presentation must define a renderable view.');
    }

    protected function extractDeckInner(string $html): ?string
    {
        if (preg_match($this->deckSectionPattern(captureInner: true), $html, $match) !== 1) {
            return null;
        }

        return $match[2];
    }

    /**
     * @return array<int, array<int, Slide>>
     */
    protected function parseStructuredSlides(string $deckInner, string $path): array
    {
        $columns = [];
        $colIndex = 0;

        $offset = 0;
        $len = strlen($deckInner);

        while ($offset < $len) {
            $nextVertical = $this->findTag($deckInner, 'section', $offset, 'slidewire-vertical-slide');
            $nextStack = $this->findTag($deckInner, 'section', $offset, 'slidewire-stack');
            $nextArticle = $this->findTag($deckInner, 'article', $offset);

            if ($nextVertical !== null && $nextStack !== null) {
                $nextStack = $nextVertical['start'] <= $nextStack['start'] ? $nextVertical : $nextStack;
            } elseif ($nextVertical !== null) {
                $nextStack = $nextVertical;
            }

            if ($nextStack === null && $nextArticle === null) {
                break;
            }

            $stackPos = $nextStack !== null ? $nextStack['start'] : PHP_INT_MAX;
            $articlePos = $nextArticle !== null ? $nextArticle['start'] : PHP_INT_MAX;

            if ($stackPos < $articlePos) {
                $stackArticles = [];
                preg_match_all('/<article\b([^>]*)>(.*?)<\/article>/is', $nextStack['inner'], $articles, PREG_SET_ORDER);

                foreach ($articles as $vIndex => $article) {
                    $stackArticles[] = $this->buildSlide($article, $path, $colIndex, $vIndex);
                }

                if ($stackArticles !== []) {
                    $columns[] = $stackArticles;
                }

                $offset = $nextStack['end'];
                ++$colIndex;
            } else {
                $columns[] = [$this->buildSlide($nextArticle['match'], $path, $colIndex, 0)];
                $offset = $nextArticle['end'];
                ++$colIndex;
            }
        }

        return $columns;
    }

    /**
     * @return array<int, array<int, Slide>>
     */
    protected function parseFlatSlides(string $html, string $path): array
    {
        preg_match_all('/<article\b([^>]*)>(.*?)<\/article>/is', $html, $slides, PREG_SET_ORDER);

        if ($slides === []) {
            return [];
        }

        return array_values(array_map(
            fn (array $slide, int $index): array => [$this->buildSlide($slide, $path, $index, 0)],
            $slides,
            array_keys($slides),
        ));
    }

    protected function buildSlide(array $match, string $path, int $hIndex, int $vIndex): Slide
    {
        $attributes = $this->extractAttributes($match[1] ?? '');
        $innerHtml = trim($match[2] ?? '');

        return new Slide(
            id: $this->slideIdGenerator->fromPath($path, $hIndex, $vIndex),
            html: $innerHtml,
            meta: $this->extractMetaFromAttributes($attributes),
            fragments: $this->fragmentCount($innerHtml),
            class: $attributes['class'] ?? '',
            h: $hIndex,
            v: $vIndex,
        );
    }

    /**
     * @return array{start: int, end: int, inner: string, match: array}|null
     */
    protected function findTag(string $html, string $tag, int $offset, ?string $requiredClass = null): ?array
    {
        $pattern = $requiredClass !== null
            ? "/<{$tag}\\b([^>]*class=(?:\"[^\"]*|\'[^\']*)" . preg_quote($requiredClass, '/') . "(?:[^\"]*\"|[^\']*\')[^>]*)>(.*?)<\\/{$tag}>/is"
            : "/<{$tag}\\b([^>]*)>(.*?)<\\/{$tag}>/is";

        if (preg_match($pattern, $html, $match, PREG_OFFSET_CAPTURE, $offset) !== 1) {
            return null;
        }

        return [
            'start' => $match[0][1],
            'end' => $match[0][1] + strlen($match[0][0]),
            'inner' => $match[2][0],
            'match' => [$match[0][0], $match[1][0], $match[2][0]],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function extractDeckMeta(string $html): array
    {
        if (preg_match($this->deckSectionPattern(), $html, $match) !== 1) {
            return [];
        }

        $attributes = $this->extractAttributes($match[1]);

        return $this->extractMetaFromAttributes($attributes);
    }

    /**
     * @return array<string, string>
     */
    protected function extractMetaFromAttributes(array $attributes): array
    {
        $meta = [];

        foreach ($attributes as $key => $value) {
            if (str_starts_with((string) $key, 'data-')) {
                $normalizedKey = str_replace(['data-', '-'], ['', '_'], strtolower((string) $key));
                $meta[$normalizedKey] = $value;
            }
        }

        return $meta;
    }

    /**
     * @return array<string, string>
     */
    protected function extractAttributes(string $rawAttributes): array
    {
        preg_match_all('/([\w-]+)\s*=\s*("|\')(.*?)\2/s', $rawAttributes, $matches, PREG_SET_ORDER);

        if ($matches === []) {
            return [];
        }

        $attributes = [];

        foreach ($matches as $attribute) {
            $attributes[strtolower($attribute[1])] = html_entity_decode($attribute[3], ENT_QUOTES);
        }

        return $attributes;
    }

    protected function fragmentCount(string $html): int
    {
        preg_match_all('/data-fragment(?:-index)?="?(\d+)?"?/', $html, $matches);

        if ($matches[0] === []) {
            return 0;
        }

        $indices = array_filter($matches[1], fn (string $value): bool => $value !== '');

        if ($indices === []) {
            return count($matches[0]);
        }

        return max(array_map(intval(...), $indices)) + 1;
    }

    protected function deckSectionPattern(bool $captureInner = false): string
    {
        if ($captureInner) {
            return '/<section\b([^>]*class=(?:"[^"]*slidewire-deck[^"]*"|\'[^\']*slidewire-deck[^\']*\')[^>]*)>(.*)<\/section>/is';
        }

        return '/<section\b([^>]*class=(?:"[^"]*slidewire-deck[^"]*"|\'[^\']*slidewire-deck[^\']*\')[^>]*)>/is';
    }

    protected function isComposablePart(string $path): bool
    {
        $filename = basename($path);

        if ($filename === 'deck.blade.php') {
            return false;
        }

        return Str::endsWith($filename, ['.blade.php', '.md']);
    }
}
