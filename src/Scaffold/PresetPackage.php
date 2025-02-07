<?php

namespace TightenCo\Jigsaw\Scaffold;

use Error;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TightenCo\Jigsaw\File\Filesystem;

class PresetPackage
{
    public const PRESETS = [
        'blog' => 'tightenco/jigsaw-blog-template',
        'docs' => 'tightenco/jigsaw-docs-template',
    ];

    public $constraint;

    public $name;

    public $shortName;

    public $path;

    public $preset;

    public $suffix;

    public $vendor;

    protected $builder;

    protected $customInstaller;

    protected $defaultInstaller;

    protected $files;

    protected $process;

    public function __construct(DefaultInstaller $default, CustomInstaller $custom, ProcessRunner $process)
    {
        $this->defaultInstaller = $default;
        $this->customInstaller = $custom;
        $this->process = $process;
        $this->files = new Filesystem;
    }

    public function init($preset, PresetScaffoldBuilder $builder)
    {
        $this->preset = $preset;
        $this->builder = $builder;
        $this->resolveNames();
        $this->resolvePath();
    }

    public function runInstaller($console)
    {
        if (! $this->files->exists($this->path . DIRECTORY_SEPARATOR . 'init.php')) {
            return $this->runDefaultInstaller();
        }

        try {
            $init = $this->customInstaller->setConsole($console)->install($this->builder);
            $initFile = include $this->path . DIRECTORY_SEPARATOR . 'init.php';

            if (is_array($initFile) && count($initFile)) {
                return $this->runDefaultInstaller($initFile);
            }
        } catch (InstallerCommandException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception("The 'init.php' file for this preset contains errors.");
        } catch (Error $e) {
            throw new Exception("The 'init.php' file for this preset contains errors.");
        }
    }

    protected function runDefaultInstaller($settings = [])
    {
        $this->defaultInstaller->install($this->builder, $settings);
    }

    protected function resolveNames()
    {
        $name = Arr::get(self::PRESETS, $this->preset, $this->preset);

        if (! Str::contains($name, '/')) {
            throw new Exception("'{$name}' is not a valid package name.");
        }

        $parts = explode('/', $name, 3);
        $this->vendor = Arr::get($parts, 0);
        $composerName = explode(':', Arr::get($parts, 1));
        $this->name = $composerName[0];
        $this->constraint = $composerName[1] ?? '';
        $this->suffix = Arr::get($parts, 2);
        $this->shortName = $this->getShortName();
    }

    protected function getShortName()
    {
        return Str::contains($this->preset, '/') ?
            explode('/', $this->preset)[1] :
            $this->preset;
    }

    protected function resolvePath()
    {
        $this->path = collect([$this->builder->base, 'vendor', $this->vendor, $this->name, $this->suffix])
            ->filter()
            ->implode(DIRECTORY_SEPARATOR);

        if (! $this->files->exists($this->path)) {
            $package = $this->vendor . '/' . $this->name;

            if (! empty($this->constraint)) {
                $package .= ':' . $this->constraint;
            }

            try {
                $this->installPackageFromComposer($package);
            } catch (Exception $e) {
                throw new Exception("The '{$package}' preset could not be found.");
            }
        }
    }

    protected function installPackageFromComposer($package)
    {
        $this->process->run('composer require ' . $package);
    }
}
