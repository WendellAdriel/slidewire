<?php

declare(strict_types=1);

namespace WendellAdriel\SlideWire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use WendellAdriel\SlideWire\Support\PresentationPathResolver;

class MakeSlideCommand extends Command
{
    protected $signature = 'make:slidewire
        {name? : The presentation path, e.g. team/q1-kickoff}
        {--presentation= : The presentation path override}
        {--title= : The first slide title}
        {--md : Create a standalone Markdown deck}
        {--multi : Create a multi-file deck}
        {--force : Overwrite existing files}';

    protected $description = 'Create a SlideWire presentation scaffold';

    public function handle(PresentationPathResolver $resolver): int
    {
        if ((bool) $this->option('md') && (bool) $this->option('multi')) {
            $this->error('The --md and --multi options cannot be combined.');

            return self::FAILURE;
        }

        $presentation = $this->resolvePresentationName();
        $title = $this->option('title') ?: $this->ask('Presentation title', 'SlideWire Presentation');

        $existingPath = $resolver->presentationPath($presentation);

        if ($existingPath !== null && ! (bool) $this->option('force')) {
            $this->error("Slide scaffold already exists at [{$existingPath}]. Use --force to overwrite.");

            return self::FAILURE;
        }

        if (is_string($existingPath) && (bool) $this->option('force')) {
            $this->deletePath($existingPath);
        }

        if ((bool) $this->option('multi')) {
            $presentationDirectory = $resolver->firstRoot() . DIRECTORY_SEPARATOR . $presentation;

            $this->createMultiFilePresentation($presentationDirectory, $presentation, $title);

            $this->info("Created SlideWire presentation [{$presentation}] at [{$presentationDirectory}].");

            return self::SUCCESS;
        }

        $presentationPath = $this->presentationPath($resolver, $presentation, (bool) $this->option('md'));

        File::ensureDirectoryExists(dirname($presentationPath));

        $stub = (bool) $this->option('md')
            ? File::get(__DIR__ . '/../../stubs/presentation-markdown.stub')
            : File::get(__DIR__ . '/../../stubs/presentation.stub');

        $contents = $this->populateStub($stub, $presentation, $title);

        File::put($presentationPath, $contents);

        $this->info("Created SlideWire presentation [{$presentation}] at [{$presentationPath}].");

        return self::SUCCESS;
    }

    protected function resolvePresentationName(): string
    {
        $presentation = $this->option('presentation') ?: $this->argument('name');

        if (is_string($presentation) && trim($presentation) !== '') {
            return trim($presentation, '/');
        }

        return trim((string) $this->ask('Presentation path', 'index'), '/');
    }

    protected function presentationPath(PresentationPathResolver $resolver, string $presentation, bool $markdown): string
    {
        $root = $resolver->firstRoot();
        $suffix = $markdown ? '.md' : '.blade.php';

        return $root . DIRECTORY_SEPARATOR . $presentation . $suffix;
    }

    protected function createMultiFilePresentation(string $presentationDirectory, string $presentation, string $title): void
    {
        File::ensureDirectoryExists($presentationDirectory);

        File::put(
            $presentationDirectory . DIRECTORY_SEPARATOR . 'deck.blade.php',
            File::get(__DIR__ . '/../../stubs/presentation-multi-deck.stub'),
        );

        File::put(
            $presentationDirectory . DIRECTORY_SEPARATOR . '01-intro.blade.php',
            $this->populateStub(
                File::get(__DIR__ . '/../../stubs/presentation-multi-slide.stub'),
                $presentation,
                $title,
            ),
        );

        File::put(
            $presentationDirectory . DIRECTORY_SEPARATOR . '02-markdown.md',
            $this->populateStub(
                File::get(__DIR__ . '/../../stubs/presentation-multi-markdown.stub'),
                $presentation,
                $title,
            ),
        );
    }

    protected function populateStub(string $stub, string $presentation, string $title): string
    {
        return str_replace(['{{ title }}', '{{ presentation }}'], [$title, $presentation], $stub);
    }

    protected function deletePath(string $path): void
    {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);

            return;
        }

        File::delete($path);
    }
}
