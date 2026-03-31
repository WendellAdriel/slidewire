<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Support;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class PresentationPathResolver
{
    public function presentationPath(string $presentation): ?string
    {
        $normalized = $this->normalizePresentationName($presentation);

        foreach ($this->roots() as $root) {
            $bladeCandidate = "{$root}" . DIRECTORY_SEPARATOR . "{$normalized}.blade.php";

            if (File::exists($bladeCandidate)) {
                return $bladeCandidate;
            }

            $markdownCandidate = "{$root}" . DIRECTORY_SEPARATOR . "{$normalized}.md";

            if (File::exists($markdownCandidate)) {
                return $markdownCandidate;
            }

            $directoryCandidate = "{$root}" . DIRECTORY_SEPARATOR . $normalized;

            if (File::isDirectory($directoryCandidate)) {
                return $directoryCandidate;
            }
        }

        return null;
    }

    public function absolutePresentationPath(string $presentation): string
    {
        $normalized = $this->normalizePresentationName($presentation);
        $root = $this->firstRoot();

        return "{$root}" . DIRECTORY_SEPARATOR . "{$normalized}.blade.php";
    }

    public function presentationDirectory(string $presentation): ?string
    {
        $path = $this->presentationPath($presentation);

        if ($path === null) {
            return null;
        }

        if (File::isDirectory($path)) {
            return $path;
        }

        return dirname($path);
    }

    public function firstRoot(): string
    {
        $roots = $this->roots();

        return $roots[0] ?? resource_path('views/pages/slides');
    }

    public function roots(): array
    {
        return array_values(array_filter(config('slidewire.presentation_roots', []), is_string(...)));
    }

    protected function normalizePresentationName(string $presentation): string
    {
        $normalized = str_replace('\\', '/', $presentation);

        do {
            $previous = $normalized;
            $normalized = str_replace('..', '', $normalized);
        } while ($normalized !== $previous);

        $normalized = trim($normalized, '/');

        if ($normalized === '') {
            throw new InvalidArgumentException('Presentation name cannot be empty.');
        }

        return $normalized;
    }
}
