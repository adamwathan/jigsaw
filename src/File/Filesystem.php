<?php

namespace TightenCo\Jigsaw\File;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Filesystem extends BaseFilesystem
{
    public function getFile($directory, $filename)
    {
        $filePath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        return new SplFileInfo($filePath, $directory, $filename);
    }

    public function putWithDirectories($file_path, $contents)
    {
        $directory_path = collect(explode('/', $file_path));
        $directory_path->pop();
        $directory_path = rightTrimPath($directory_path->implode('/'));

        if (! $this->isDirectory($directory_path)) {
            $this->makeDirectory($directory_path, 0755, true);
        }

        $this->put($file_path, $contents);
    }

    public function files($directory, $match = [], $ignore = [], $ignore_dotfiles = false)
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles)->files(),
            false,
        ) : [];
    }

    public function directories($directory, $match = [], $ignore = [], $ignore_dotfiles = false)
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles)->directories(),
            false,
        ) : [];
    }

    public function filesAndDirectories($directory, $match = [], $ignore = [], $ignore_dotfiles = false)
    {
        return $directory ? iterator_to_array(
            $this->getFinder($directory, $match, $ignore, $ignore_dotfiles),
            false,
        ) : [];
    }

    public function isEmptyDirectory($directory, $ignoreDotFiles = false)
    {
        return $this->exists($directory) ? count($this->files($directory)) == 0 : false;
    }

    protected function getFinder($directory, $match = [], $ignore = [], $ignore_dotfiles = false)
    {
        $finder = Finder::create()
            ->in($directory)
            ->ignoreDotFiles($ignore_dotfiles)
            ->notName('.DS_Store');

        collect($match)->each(function ($pattern) use ($finder) {
            $finder->path($this->getWildcardRegex($pattern));
        });

        collect($ignore)->each(function ($pattern) use ($finder) {
            $finder->notPath($this->getWildcardRegex($pattern));
        });

        return $finder->sortByName();
    }

    protected function getWildcardRegex($pattern)
    {
        return '#^' . str_replace('\*', '[^/]+', preg_quote(trim($pattern, '/'))) . '($|/)#';
    }
}
