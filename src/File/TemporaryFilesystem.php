<?php

namespace TightenCo\Jigsaw\File;

use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class TemporaryFilesystem
{
    private $tempPath;

    private $filesystem;

    public function __construct($tempPath, $filesystem = null)
    {
        $this->tempPath = $tempPath;
        $this->filesystem = $filesystem ?: new Filesystem;
    }

    public function buildTempPath($filename, $extension)
    {
        return $this->tempPath . DIRECTORY_SEPARATOR .
            ($filename ? sha1($filename) : Str::random(32)) .
            $extension;
    }

    public function get($originalFilename, $extension)
    {
        $file = new SplFileInfo(
            $this->buildTempPath($originalFilename, $extension),
            $this->tempPath,
            $originalFilename . $extension,
        );

        return $file->isReadable() ? new InputFile($file) : null;
    }

    public function put($contents, $filename, $extension)
    {
        $path = $this->buildTempPath($filename, $extension);
        $this->filesystem->put($path, $contents);

        return $path;
    }

    public function hasTempDirectory()
    {
        return $this->filesystem->exists($this->tempPath);
    }

    private function delete($path)
    {
        $this->filesystem->delete($path);
    }
}
