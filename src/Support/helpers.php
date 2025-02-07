<?php

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\VarDumper\VarDumper;

if (! function_exists('app')) {
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        if (is_null($abstract)) {
            return \TightenCo\Jigsaw\Container::getInstance();
        }

        return \TightenCo\Jigsaw\Container::getInstance()->make($abstract, $parameters);
    }
}

function leftTrimPath($path)
{
    return ltrim($path, ' \\/');
}

function rightTrimPath($path)
{
    return rtrim($path ?? '', ' .\\/');
}

function trimPath($path)
{
    return rightTrimPath(leftTrimPath($path));
}

function resolvePath($path)
{
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $segments = [];

    collect(explode(DIRECTORY_SEPARATOR, $path))
        ->filter(fn ($part) => (string) $part !== '')
        ->each(function ($part) use (&$segments) {
            if ($part == '..') {
                array_pop($segments);
            } elseif ($part != '.') {
                $segments[] = $part;
            }
        });

    return implode(DIRECTORY_SEPARATOR, $segments);
}

/**
 * Get the path to the public folder.
 */
function public_path($path = '')
{
    $c = Container::getInstance();
    $source = Arr::get($c['config'], 'build.source', 'source');

    return $source . ($path ? '/' . ltrim($path, '/') : $path);
}

/**
 * Get the path to a versioned Elixir file.
 */
function elixir($file, $buildDirectory = 'build')
{
    static $manifest;
    static $manifestPath;

    if (is_null($manifest) || $manifestPath !== $buildDirectory) {
        $manifest = json_decode(file_get_contents(public_path($buildDirectory . '/rev-manifest.json')), true);

        $manifestPath = $buildDirectory;
    }

    if (isset($manifest[$file])) {
        return '/' . trim($buildDirectory . '/' . $manifest[$file], '/');
    }

    throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
}

/**
 * Get the path to a versioned Mix file.
 */
function mix($path, $manifestDirectory = 'assets')
{
    static $manifests = [];

    if (! Str::startsWith($path, '/')) {
        $path = "/{$path}";
    }

    if ($manifestDirectory && ! Str::startsWith($manifestDirectory, '/')) {
        $manifestDirectory = "/{$manifestDirectory}";
    }

    if (file_exists(public_path($manifestDirectory . '/hot'))) {
        return new HtmlString("//localhost:8080{$path}");
    }

    $manifestPath = public_path($manifestDirectory . '/mix-manifest.json');

    if (! isset($manifests[$manifestPath])) {
        if (! file_exists($manifestPath)) {
            throw new Exception('The Mix manifest does not exist.');
        }

        $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
    }

    $manifest = $manifests[$manifestPath];

    if (! isset($manifest[$path])) {
        throw new InvalidArgumentException("Unable to locate Mix file: {$path}.");
    }

    return new HtmlString($manifestDirectory . $manifest[$path]);
}

if (! function_exists('url')) {
    function url(string $path): string
    {
        $c = Container::getInstance();

        return trim($c['config']['baseUrl'], '/') . '/' . trim($path, '/');
    }
}

if (! function_exists('dd')) {
    function dd(...$args)
    {
        foreach ($args as $x) {
            (new VarDumper)->dump($x);
        }

        exit(1);
    }
}

function inline($assetPath)
{
    preg_match('/^\/assets\/build\/(css|js)\/.*\.(css|js)/', $assetPath, $matches);

    if (! count($matches)) {
        throw new InvalidArgumentException("Given asset path is not valid: {$assetPath}");
    }

    $pathParts = explode('?', $assetPath);

    return new HtmlString(file_get_contents("source{$pathParts[0]}"));
}
