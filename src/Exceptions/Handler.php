<?php

namespace TightenCo\Jigsaw\Exceptions;

use Closure;
use Illuminate\Console\View\Components\BulletList;
use Illuminate\Console\View\Components\Error;
use Illuminate\Console\View\Components\Warn;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Traits\ReflectsClosures;
use Illuminate\View\ViewException;
use InvalidArgumentException;
use NunoMaduro\Collision\Adapters\Laravel\Inspector;
use NunoMaduro\Collision\Provider;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleExceptionInterface;
use Throwable;

class Handler implements ExceptionHandler
{
    use ReflectsClosures;

    /** @var array<string, Closure> */
    private array $exceptionMap = [];

    public function report(Throwable $e): void
    {
        //
    }

    public function shouldReport(Throwable $e): bool
    {
        return true;
    }

    public function render($request, Throwable $e): void
    {
        //
    }

    /**
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     */
    public function renderForConsole($output, Throwable $e): void
    {
        if ($e instanceof CommandNotFoundException) {
            $message = str($e->getMessage())->explode('.')->first();

            if (! empty($alternatives = $e->getAlternatives())) {
                (new Error($output))->render("{$message}. Did you mean one of these?");
                (new BulletList($output))->render($alternatives);
            } else {
                (new Error($output))->render($message);
            }

            return;
        }

        if ($e instanceof SymfonyConsoleExceptionInterface) {
            (new ConsoleApplication)->renderThrowable($e, $output);

            return;
        }

        if ($e instanceof DeprecationException) {
            // If the deprecation appears to have come from a compiled Blade view, wrap it in
            // a ViewException and map it manually so Ignition will add the uncompiled path
            if (preg_match('/cache\/\w+\.php$/', $e->getFile()) === 1) {
                $e = $this->mapException(
                    new ViewException("{$e->getMessage()} (View: )", 0, 1, $e->getFile(), $e->getLine(), $e),
                );
            }

            (new Warn($output))->render("{$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}");

            return;
        }

        $e = $this->mapException($e);

        /** @var \NunoMaduro\Collision\Provider $provider */
        $provider = app(Provider::class);

        $handler = $provider->register()->getHandler()->setOutput($output);
        $handler->setInspector(new Inspector($e));

        $handler->handle();
    }

    public function map(Closure|string $from, Closure|string $to = null): static
    {
        if (is_string($to)) {
            $to = fn ($exception) => new $to('', 0, $exception);
        }

        if (is_callable($from) && is_null($to)) {
            $from = $this->firstClosureParameterType($to = $from);
        }

        if (! is_string($from) || ! $to instanceof Closure) {
            throw new InvalidArgumentException('Invalid exception mapping.');
        }

        $this->exceptionMap[$from] = $to;

        return $this;
    }

    protected function mapException(Throwable $e): Throwable
    {
        if (method_exists($e, 'getInnerException') && ($inner = $e->getInnerException()) instanceof Throwable) {
            return $inner;
        }

        foreach ($this->exceptionMap as $class => $mapper) {
            if ($e instanceof $class) {
                return $mapper($e);
            }
        }

        return $e;
    }
}
