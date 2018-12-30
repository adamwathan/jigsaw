<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TightenCo\Jigsaw\File\ConfigFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\PathResolvers\PrettyOutputPathResolver;

class BuildCommand extends Command
{
    private $app;
    private $consoleOutput;

    public function __construct($app)
    {
        $this->app = $app;
        $this->consoleOutput = $app->consoleOutput;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('build')
            ->setDescription('Build your site.')
            ->addArgument('env', InputArgument::OPTIONAL, 'What environment should we use to build?', 'local')
            ->addOption('pretty', null, InputOption::VALUE_REQUIRED, 'Should the site use pretty URLs?', 'true')
            ->addOption('cache', 'c', InputOption::VALUE_OPTIONAL, 'Should a cache be used when building the site?', 'false');
    }

    protected function fire(): void
    {
        $startTime = microtime(true);
        $env = $this->input->getArgument('env');
        $this->includeEnvironmentConfig($env);
        $this->updateBuildPaths($env);
        $cacheExists = $this->app[TemporaryFilesystem::class]->hasTempDirectory();

        if ($this->input->getOption('pretty') === 'true') {
            $this->app->instance('outputPathResolver', new PrettyOutputPathResolver());
        }

        if ($this->input->getOption('quiet')) {
            $verbosity = OutputInterface::VERBOSITY_QUIET;
        } elseif ($this->input->getOption('verbose')) {
            $verbosity = OutputInterface::VERBOSITY_VERBOSE;
        } else {
            $verbosity = OutputInterface::VERBOSITY_NORMAL;
        }

        $this->consoleOutput->setup($verbosity);
        $this->consoleOutput->writeIntro($env, $this->useCache(), $cacheExists);

        if ($this->confirmDestination()) {
            $this->app->make(Jigsaw::class)->build($env, $this->useCache());

            $this->consoleOutput
                ->writeTime(round(microtime(true) - $startTime, 2), $this->useCache(), $cacheExists)
                ->writeConclusion();
        }
    }

    private function useCache(): bool
    {
        return $this->input->getOption('cache') !== 'false' || $this->app->config->get('cache');
    }

    private function includeEnvironmentConfig($env): void
    {
        $environmentConfigPath = $this->getAbsolutePath("config.{$env}.php");
        $environmentConfig = (new ConfigFile($environmentConfigPath))->config;

        $this->app->config = collect($this->app->config)
            ->merge(collect($environmentConfig))
            ->filter(function ($item): bool {
                return $item !== null;
            });
    }

    private function updateBuildPaths($env): void
    {
        $this->app->buildPath = [
            'source' => $this->getBuildPath('source', $env),
            'destination' => $this->getBuildPath('destination', $env),
        ];
    }

    private function getBuildPath($pathType, $env): string
    {
        $customPath = array_get($this->app->config, 'build.' . $pathType);
        $buildPath = $customPath ? $this->getAbsolutePath($customPath) : $this->app->buildPath[$pathType];

        return str_replace('{env}', $env, $buildPath);
    }

    private function getAbsolutePath($path): string
    {
        return $this->app->cwd . '/' . trimPath($path);
    }

    private function confirmDestination(): bool
    {
        if (! $this->input->getOption('quiet')) {
            $customPath = array_get($this->app->config, 'build.destination');

            if ($customPath && strpos($customPath, 'build_') !== 0) {
                return $this->confirm('Overwrite "' . $this->app->buildPath['destination'] . '"? ');
            }
        }

        return true;
    }
}
