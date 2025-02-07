<?php

namespace Tests;

use Illuminate\Support\Arr;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Scaffold\DefaultInstaller;
use TightenCo\Jigsaw\Scaffold\PresetPackage;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\ProcessRunner;

class DefaultScaffoldInstallerTest extends TestCase
{
    #[Test]
    public function installer_installs_basic_scaffold_files()
    {
        $this->createSource([]);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($this->tmp);

        $this->assertCount(0, app('files')->filesAndDirectories($this->tmp));

        (new DefaultInstaller)->install($builder, ['commands' => []]);

        $this->assertFileExists($this->tmpPath('source'));
        $this->assertFileExists($this->tmpPath('package.json'));
        $this->assertFileExists($this->tmpPath('webpack.mix.js'));
        $this->assertFileExists($this->tmpPath('config.php'));
    }

    #[Test]
    public function installer_deletes_single_base_file_specified_in_delete_array()
    {
        $this->createSource([]);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, [
            'delete' => 'config.php',
            'commands' => [],
        ]);

        $this->assertFileMissing($this->tmpPath('config.php'));
        $this->assertFileExists($this->tmpPath('source'));
    }

    #[Test]
    public function installer_deletes_multiple_base_files_specified_in_delete_array()
    {
        $this->createSource([]);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, [
            'delete' => ['config.php', 'package.json'],
            'commands' => [],
        ]);

        $this->assertFileMissing($this->tmpPath('config.php'));
        $this->assertFileMissing($this->tmpPath('package.json'));
        $this->assertFileExists($this->tmpPath('source'));
    }

    #[Test]
    public function installer_deletes_base_directories_specified_in_delete_array()
    {
        $this->createSource([]);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, [
            'delete' => ['source'],
            'commands' => [],
        ]);

        $this->assertFileMissing($this->tmpPath('source'));
    }

    #[Test]
    public function installer_copies_all_preset_files()
    {
        $this->createSource([
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
                'other' => [
                    'source-file.md' => '',
                    'nested' => [
                        'nested-file.md' => '',
                    ],
                ],
                'empty' => [],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => []]);

        $this->assertFileExists($this->tmpPath('.dotfile'));
        $this->assertFileExists($this->tmpPath('preset-file.php'));
        $this->assertFileExists($this->tmpPath('source/source-file.md'));
        $this->assertFileExists($this->tmpPath('other/nested/nested-file.md'));
        $this->assertFileExists($this->tmpPath('empty'));
    }

    #[Test]
    public function installer_preserves_base_files_when_copying_preset_files()
    {
        $this->createSource([
            'package' => [
                'preset-file.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => []]);

        $this->assertFileExists($this->tmpPath('config.php'));
    }

    #[Test]
    public function installer_overwrites_base_files_of_same_name_when_copying_preset_files()
    {
        $this->createSource([
            'package' => [
                'config.php' => 'new config file from preset',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => []]);

        $this->assertOutputFile('config.php', 'new config file from preset');
    }

    #[Test]
    public function installer_can_ignore_files_and_directories_from_preset_when_copying()
    {
        $this->createSource([
            'package' => [
                'copy-this.php' => '',
                'ignore-this.php' => '',
                'copy-directory' => [
                    'copy-this.php' => '',
                ],
                'ignore-directory' => [
                    'ignore-this.php' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, [
            'ignore' => [
                'ignore-this.php',
                'ignore-directory',
            ],
            'commands' => [],
        ]);

        $this->assertFileExists($this->tmpPath('copy-this.php'));
        $this->assertFileMissing($this->tmpPath('ignore-this.php'));
        $this->assertFileExists($this->tmpPath('copy-directory'));
        $this->assertFileMissing($this->tmpPath('ignore-directory'));
    }

    #[Test]
    public function installer_can_ignore_files_and_directories_from_preset_using_wildcard_when_copying()
    {
        $this->createSource([
            'package' => [
                'copy-this.php' => '',
                'ignore-this.php' => '',
                'copy-directory' => [
                    'copy-this.php' => '',
                ],
                'ignore-directory' => [
                    'ignore-this.php' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, [
            'ignore' => [
                'ignore*',
            ],
            'commands' => [],
        ]);

        $this->assertFileExists($this->tmpPath('copy-this.php'));
        $this->assertFileMissing($this->tmpPath('ignore-this.php'));
        $this->assertFileExists($this->tmpPath('copy-directory'));
        $this->assertFileMissing($this->tmpPath('ignore-directory'));
    }

    #[Test]
    public function installer_ignores_node_modules_directory_from_preset_when_copying()
    {
        $this->createSource([
            'package' => [
                'node_modules' => [],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => []]);

        $this->assertFileMissing($this->tmpPath('node_modules'));
    }

    #[Test]
    public function installer_ignores_vendor_directory_from_preset_when_copying()
    {
        $this->createSource([
            'package' => [
                'vendor' => [],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => []]);

        $this->assertFileMissing($this->tmpPath('vendor'));
    }

    #[Test]
    public function installer_ignores_init_file_from_preset_when_copying()
    {
        $this->createSource([
            'package' => [
                'init.php' => 'the init file',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => []]);

        $this->assertFileMissing($this->tmpPath('init.php'));
    }

    #[Test]
    public function installer_ignores_build_directories_from_preset_when_copying()
    {
        $this->createSource([
            'package' => [
                'build_local' => [],
                'build_production' => [],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => []]);

        $this->assertFileMissing($this->tmpPath('build_local'));
        $this->assertFileMissing($this->tmpPath('build_production'));
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function installer_runs_default_commands_if_none_are_specified_in_init()
    {
        $builder = Mockery::spy(PresetScaffoldBuilder::class);
        $builder->shouldReceive('buildBasicScaffold')->andReturn($builder);
        $builder->shouldReceive('cacheComposerDotJson')->andReturn($builder);
        $builder->shouldReceive('deleteSiteFiles')->andReturn($builder);
        $builder->shouldReceive('copyPresetFiles')->andReturn($builder);
        $builder->shouldReceive('mergeComposerDotJson')->andReturn($builder);

        $installer = new DefaultInstaller;
        $installer->install($builder);

        $builder->shouldHaveReceived('runCommands')->with($installer::DEFAULT_COMMANDS);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function installer_runs_no_commands_if_empty_array_is_specified_in_init()
    {
        $builder = Mockery::spy(PresetScaffoldBuilder::class);
        $builder->shouldReceive('buildBasicScaffold')->andReturn($builder);
        $builder->shouldReceive('cacheComposerDotJson')->andReturn($builder);
        $builder->shouldReceive('deleteSiteFiles')->andReturn($builder);
        $builder->shouldReceive('copyPresetFiles')->andReturn($builder);
        $builder->shouldReceive('mergeComposerDotJson')->andReturn($builder);

        $installer = new DefaultInstaller;
        $installer->install($builder, ['commands' => []]);

        $builder->shouldHaveReceived('runCommands')->with([]);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function installer_runs_specified_commands_from_init()
    {
        $builder = Mockery::spy(PresetScaffoldBuilder::class);
        $builder->shouldReceive('buildBasicScaffold')->andReturn($builder);
        $builder->shouldReceive('cacheComposerDotJson')->andReturn($builder);
        $builder->shouldReceive('deleteSiteFiles')->andReturn($builder);
        $builder->shouldReceive('copyPresetFiles')->andReturn($builder);
        $builder->shouldReceive('mergeComposerDotJson')->andReturn($builder);

        $installer = new DefaultInstaller;
        $installer->install($builder, ['commands' => ['yarn']]);

        $builder->shouldHaveReceived('runCommands')->with(['yarn']);
    }

    #[Test]
    public function composer_json_is_restored_if_deleted_after_preset_is_installed()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $this->createSource([
            'composer.json' => json_encode($old_composer),
            'package' => [
                'preset-file.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => [], 'delete' => 'composer.json']);

        $this->assertFileExists($this->tmpPath('composer.json'));
        $this->assertEquals($old_composer, json_decode(file_get_contents($this->tmpPath('composer.json')), true));
    }

    #[Test]
    public function original_composer_json_is_merged_with_composer_json_installed_by_preset()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $this->createSource([
            'composer.json' => json_encode($old_composer),
            'package' => [
                'preset-file.php' => '',
                'composer.json' => json_encode([
                    'repository' => 'setting',
                    'require' => [
                        'other/dependency' => '5.1',
                    ],
                ]),
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => [], 'delete' => 'composer.json']);

        $new_composer = json_decode(file_get_contents($this->tmpPath('composer.json')), true);

        $this->assertEquals('^1.2', Arr::get($new_composer, 'require.tightenco/jigsaw'));
        $this->assertEquals('5.1', Arr::get($new_composer, 'require.other/dependency'));
        $this->assertEquals('setting', Arr::get($new_composer, 'repository'));
    }

    #[Test]
    public function version_constraints_from_original_composer_json_take_precedence_in_merged_composer_json()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $this->createSource([
            'composer.json' => json_encode($old_composer),
            'package' => [
                'preset-file.php' => '',
                'composer.json' => json_encode([
                    'require' => [
                        'tightenco/jigsaw' => '1.0',
                        'new-package/test' => '3.0',
                    ],
                ]),
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => [], 'delete' => 'composer.json']);

        $new_composer = json_decode(file_get_contents($this->tmpPath('composer.json')), true);

        $this->assertEquals('^1.2', Arr::get($new_composer, 'require.tightenco/jigsaw'));
        $this->assertEquals('1.0', Arr::get($new_composer, 'require.test/preset'));
        $this->assertEquals('3.0', Arr::get($new_composer, 'require.new-package/test'));
    }

    #[Test]
    public function empty_composer_json_is_created_if_it_was_not_present_before_preset_was_installed()
    {
        $this->createSource([
            'package' => [
                'preset-file.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new DefaultInstaller)->install($builder, ['commands' => [], 'delete' => 'composer.json']);

        $this->assertEquals(
            [
                'require' => [],
            ],
            json_decode(file_get_contents($this->tmpPath('composer.json')), true),
        );
    }
}
