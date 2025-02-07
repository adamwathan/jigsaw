<?php

namespace Tests;

use Closure;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Mockery;
use PHPUnit\Framework\TestCase as PHPUnit;
use TightenCo\Jigsaw\Bootstrap\HandleExceptions;
use TightenCo\Jigsaw\Container;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\Jigsaw;
use TightenCo\Jigsaw\Loaders\DataLoader;
use TightenCo\Jigsaw\PathResolvers\PrettyOutputPathResolver;

class TestCase extends PHPUnit
{
    use Haiku;

    public $sourcePath = __DIR__ . '/snapshots/default/source';

    public $destinationPath = __DIR__ . '/snapshots/default/build_local';

    protected Container $app;

    protected Filesystem $filesystem;

    protected string $tmp;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->filesystem = new Filesystem;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTmp();

        $this->app = new Container;
        /* @internal The '__testing' binding is for Jigsaw development only and may be removed. */
        $this->app->instance('__testing', true);
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \TightenCo\Jigsaw\Exceptions\Handler::class,
        );
        $this->app->bootstrapWith([
            \TightenCo\Jigsaw\Bootstrap\HandleExceptions::class,
        ]);

        $this->app->buildPath = [
            'source' => $this->sourcePath,
            'views' => $this->sourcePath,
            'destination' => $this->destinationPath,
        ];
    }

    protected function createTmp(): void
    {
        mkdir($this->tmp = __DIR__ . '/fixtures/tmp/' . static::haiku());

        // TODO this creates Jigsaw's cache directory in the root of this repo
        $this->filesystem->ensureDirectoryExists(app()->cachePath());
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            $this->app->flush();
        }

        Mockery::close();

        if (method_exists(Component::class, 'flushCache')) {
            Component::flushCache();
            Component::forgetComponentsResolver();
            Component::forgetFactory();
        }

        if ($this->status()->isSuccess()) {
            $this->filesystem->deleteDirectories(__DIR__ . '/fixtures/tmp/');
            $this->filesystem->deleteDirectory(app()->cachePath());
        }

        HandleExceptions::flushState();

        parent::tearDown();
    }

    public function getInputFile($filename)
    {
        $sourceFile = $this->filesystem->getFile(
            Str::finish($this->sourcePath, '/') . pathinfo($filename)['dirname'], basename($filename),
        );

        return new InputFile($sourceFile, $this->sourcePath);
    }

    protected function tmpPath(string $path): string
    {
        return "{$this->tmp}/{$path}";
    }

    /**
     * @deprecated Use createSource instead.
     */
    protected function setupSource($source = [])
    {
        $this->createSource(['source' => $source]);

        return new class($this->tmpPath(''))
        {
            public function __construct(
                protected string $tmp,
            ) {}

            public function hasChild($path)
            {
                return app('files')->exists($this->tmp . $path);
            }

            public function getChild($path)
            {
                return new class($this->tmp, $path)
                {
                    public function __construct(
                        protected string $tmp,
                        protected string $path,
                    ) {}

                    public function getContent()
                    {
                        return app('files')->get($this->tmp . $this->path);
                    }

                    public function filemtime()
                    {
                        return app('files')->lastModified($this->tmp . $this->path);
                    }

                    public function getChildren()
                    {
                        return app('files')->files($this->tmp . $this->path);
                    }
                };
            }
        };
    }

    protected function createSource(array $files): void
    {
        $create = function (string $prefix, array $files, Closure $create) {
            foreach ($files as $path => $contents) {
                if (is_array($contents)) {
                    app('files')->ensureDirectoryExists("{$prefix}/{$path}");
                    $create("{$prefix}/{$path}", $contents, $create);
                } else {
                    app('files')->put("{$prefix}/{$path}", $contents);
                }
            }
        };

        $create($this->tmp, $files, $create);
    }

    protected function buildSiteData($vfs = null, $config = [])
    {
        $this->app->consoleOutput->setup($verbosity = -1);
        $loader = $this->app->make(DataLoader::class);
        $siteData = $loader->loadSiteData($config);
        $collectionData = $loader->loadCollectionData($siteData, "{$this->tmp}/source");

        return $siteData->addCollectionData($collectionData);
    }

    public function buildSite($vfs = null, $config = [], $pretty = false, $viewPath = '/source')
    {
        $this->app->consoleOutput->setup($verbosity = -1);
        $this->app->config = collect($this->app->config)->merge($config);

        if ($collections = value($this->app->config->get('collections'))) {
            $this->app->config->put('collections', collect($collections)->flatMap(function ($value, $key) {
                return is_array($value) ? [$key => $value] : [$value => []];
            }));
        }

        $this->app->buildPath = [
            'source' => "{$this->tmp}/source",
            'views' => "{$this->tmp}/{$viewPath}",
            'destination' => "{$this->tmp}/build",
        ];

        if ($pretty) {
            $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        }

        return $this->app
            ->make(Jigsaw::class)
            ->build('test');
    }

    public function clean($output)
    {
        return str_replace("\n", '', $output);
    }

    protected function fixDirectorySlashes(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    protected function assertOutputFile(string $path, string $contents, ?string $message = null): void
    {
        static::assertStringEqualsFile(
            $this->tmpPath($path),
            trim($contents),
            $message ??= Str::after($this->tmp, __DIR__ . '/fixtures/tmp/') . "\n",
        );
    }

    protected function assertFileMissing(string $path): void
    {
        static::assertFileDoesNotExist($path);
    }
}
