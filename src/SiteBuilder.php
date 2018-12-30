<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw;

use Illuminate\Support\Collection;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\Handlers\DefaultHandler;

class SiteBuilder
{
    private $cachePath;
    private $files;
    private $handlers;
    private $outputPathResolver;
    private $consoleOutput;
    private $useCache;

    public function __construct(Filesystem $files, $cachePath, $outputPathResolver, $consoleOutput, $handlers = [])
    {
        $this->files = $files;
        $this->cachePath = $cachePath;
        $this->outputPathResolver = $outputPathResolver;
        $this->consoleOutput = $consoleOutput;
        $this->handlers = $handlers;
    }

    public function setUseCache($useCache): SiteBuilder
    {
        $this->useCache = $useCache;

        return $this;
    }

    public function build($source, $destination, $siteData): Collection
    {
        $this->prepareDirectory($this->cachePath, ! $this->useCache);
        $generatedFiles = $this->generateFiles($source, $siteData);
        $this->prepareDirectory($destination);
        $outputFiles = $this->writeFiles($generatedFiles, $destination);
        $this->cleanup();

        return $outputFiles;
    }

    public function registerHandler($handler): void
    {
        $this->handlers[] = $handler;
    }

    private function prepareDirectories($directories): void
    {
        foreach ($directories as $directory) {
            $this->prepareDirectory($directory, true);
        }
    }

    private function prepareDirectory($directory, $clean = false): void
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($clean) {
            $this->files->cleanDirectory($directory);
        }
    }

    private function cleanup(): void
    {
        if (! $this->useCache) {
            $this->files->deleteDirectory($this->cachePath);
        }
    }

    private function generateFiles($source, $siteData): Collection
    {
        $files = collect($this->files->allFiles($source));
        $this->consoleOutput->startProgressBar('build', $files->count());

        $files = $files->map(function ($file): InputFile {
            return new InputFile($file);
        })->flatMap(function ($file) use ($siteData): Collection {
            $this->consoleOutput->progressBar('build')->advance();

            return $this->handle($file, $siteData);
        });

        return $files;
    }

    private function writeFiles($files, $destination): Collection
    {
        $this->consoleOutput->writeWritingFiles();

        return $files->map(function ($file) use ($destination): string {
            return $this->writeFile($file, $destination);
        });
    }

    private function writeFile($file, $destination): string
    {
        $directory = $this->getOutputDirectory($file);
        $this->prepareDirectory("{$destination}/{$directory}");
        $file->putContents("{$destination}/{$this->getOutputPath($file)}");

        return $this->getOutputLink($file);
    }

    private function handle($file, $siteData): Collection
    {
        $meta = $this->getMetaData($file, $siteData->page->baseUrl);

        return $this->getHandler($file)->handle($file, PageData::withPageMetaData($siteData, $meta));
    }

    private function getHandler($file): ?DefaultHandler // TODO improve return type by using interface
    {
        return collect($this->handlers)->first(function ($handler) use ($file): bool {
            return $handler->shouldHandle($file);
        });
    }

    private function getMetaData($file, $baseUrl): array
    {
        $filename = $file->getFilenameWithoutExtension();
        $extension = $file->getFullExtension();
        $path = rightTrimPath($this->outputPathResolver->link($file->getRelativePath(), $filename, $file->getExtraBladeExtension() ?: 'html'));
        $url = rightTrimPath($baseUrl) . '/' . trimPath($path);

        return compact('filename', 'baseUrl', 'path', 'extension', 'url');
    }

    private function getOutputDirectory($file): string
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return urldecode(dirname($permalink));
        }

        return urldecode($this->outputPathResolver->directory($file->path(), $file->name(), $file->extension(), $file->page()));
    }

    private function getOutputPath($file): string
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return $permalink;
        }

        return resolvePath(urldecode($this->outputPathResolver->path(
            $file->path(),
            $file->name(),
            $file->extension(),
            $file->page()
        )));
    }

    private function getOutputLink($file): string
    {
        if ($permalink = $this->getFilePermalink($file)) {
            return $permalink;
        }

        return rightTrimPath(urldecode($this->outputPathResolver->link(
            $file->path(),
            $file->name(),
            $file->extension(),
            $file->page()
        )));
    }

    private function getFilePermalink($file): ?string
    {
        return $file->data()->page->permalink ? resolvePath(urldecode($file->data()->page->permalink)) : null;
    }
}
