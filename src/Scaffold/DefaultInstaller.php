<?php

declare(strict_types=1);

namespace TightenCo\Jigsaw\Scaffold;

class DefaultInstaller
{
    const ALWAYS_IGNORE = [
        'build_*',
        'init.php',
        'node_modules',
        'vendor',
    ];

    const DEFAULT_COMMANDS = [
        'composer install',
        'npm install',
        'npm run local',
    ];

    /** @var string[] */
    protected $commands;

    /** @var string[] */
    protected $delete;

    /** @var string[] */
    protected $ignore;

    /** @var PresetScaffoldBuilder */
    protected $builder;

    public function install(ScaffoldBuilder $builder, array $settings = []): void
    {
        $this->builder = $builder;
        $this->delete = array_get($settings, 'delete', []);
        $this->ignore = array_merge(self::ALWAYS_IGNORE, array_get($settings, 'ignore', []));
        $commands = array_get($settings, 'commands');
        $this->commands = $commands !== null ? $commands : self::DEFAULT_COMMANDS;
        $this->execute();
    }

    public function execute(): PresetScaffoldBuilder
    {
        $this->builder
            ->buildBasicScaffold()
            ->cacheComposerDotJson();

        return $this->builder
            ->deleteSiteFiles($this->delete)
            ->copyPresetFiles([], $this->ignore)
            ->mergeComposerDotJson()
            ->runCommands($this->commands);
    }
}
