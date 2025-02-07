<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use TightenCo\Jigsaw\Console\ConsoleSession;
use TightenCo\Jigsaw\File\Filesystem;
use TightenCo\Jigsaw\Scaffold\CustomInstaller;
use TightenCo\Jigsaw\Scaffold\PresetPackage;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\ProcessRunner;

class CustomScaffoldInstallerTest extends TestCase
{
    #[Test]
    public function custom_installer_installs_basic_scaffold_files()
    {
        $this->createSource([]);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($this->tmp);

        $this->assertCount(0, app('files')->filesAndDirectories($this->tmp));

        (new CustomInstaller)->install($builder)
            ->setup();

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

        (new CustomInstaller)->install($builder)
            ->setup()
            ->delete('config.php');

        $this->assertFileMissing($this->tmpPath('config.php'));
        $this->assertFileExists($this->tmpPath('source'));
    }

    #[Test]
    public function installer_deletes_multiple_base_files_specified_in_delete_array()
    {
        $this->createSource([]);
        $builder = new PresetScaffoldBuilder(new Filesystem, Mockery::mock(PresetPackage::class), new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->delete([
                'config.php',
                'package.json',
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

        (new CustomInstaller)->install($builder)
            ->setup()
            ->delete([
                'source',
            ]);

        $this->assertFileMissing($this->tmpPath('source'));
    }

    #[Test]
    public function installer_copies_all_preset_files_if_copy_has_no_parameter()
    {
        $this->createSource([
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->copy();

        $this->assertFileExists($this->tmpPath('.dotfile'));
        $this->assertFileExists($this->tmpPath('preset-file.php'));
        $this->assertFileExists($this->tmpPath('source/source-file.md'));
    }

    #[Test]
    public function installer_copies_individual_preset_file_if_copy_parameter_is_string()
    {
        $this->createSource([
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->copy('preset-file.php');

        $this->assertFileExists($this->tmpPath('preset-file.php'));
        $this->assertFileMissing($this->tmpPath('.dotfile'));
        $this->assertFileMissing($this->tmpPath('source/source-file.md'));
    }

    #[Test]
    public function installer_copies_multiple_preset_files_if_copy_parameter_is_array()
    {
        $this->createSource([
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->copy([
                'preset-file.php',
                'source/source-file.md',
            ]);

        $this->assertFileExists($this->tmpPath('preset-file.php'));
        $this->assertFileExists($this->tmpPath('source/source-file.md'));
        $this->assertFileMissing($this->tmpPath('.dotfile'));
    }

    #[Test]
    public function installer_can_copy_files_using_a_wildcard()
    {
        $this->createSource([
            'package' => [
                '.dotfile' => '',
                'preset-file-1.php' => '',
                'preset-file-2.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->copy([
                'preset-file-*.php',
            ]);

        $this->assertFileExists($this->tmpPath('preset-file-1.php'));
        $this->assertFileExists($this->tmpPath('preset-file-1.php'));
        $this->assertFileMissing($this->tmpPath('.dotfile'));
    }

    #[Test]
    public function installer_can_call_copy_multiple_times()
    {
        $this->createSource([
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->copy('.dotfile')
            ->copy('source');

        $this->assertFileExists($this->tmpPath('.dotfile'));
        $this->assertFileExists($this->tmpPath('source/source-file.md'));
        $this->assertFileMissing($this->tmpPath('preset-file.php'));
    }

    #[Test]
    public function installer_copies_from_specified_directory_to_root_if_from_is_specified()
    {
        $this->createSource([
            'package' => [
                'config.php' => 'config root',
                'themes' => [
                    'directory-1' => [
                        'config.php' => 'config 1',
                    ],
                    'directory-2' => [
                        'config.php' => 'config 2',
                    ],
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->from('themes/directory-2')
            ->copy();

        $this->assertFileExists($this->tmpPath('config.php'));
        $this->assertOutputFile('config.php', 'config 2');
    }

    #[Test]
    public function installer_can_ignore_preset_files_when_copying()
    {
        $this->createSource([
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->ignore('.dotfile')
            ->copy();

        $this->assertFileExists($this->tmpPath('preset-file.php'));
        $this->assertFileExists($this->tmpPath('source/source-file.md'));
        $this->assertFileMissing($this->tmpPath('.dotfile'));
    }

    #[Test]
    public function installer_can_call_ignore_multiple_times()
    {
        $this->createSource([
            'package' => [
                '.dotfile' => '',
                'preset-file.php' => '',
                'source' => [
                    'source-file.md' => '',
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->ignore('.dotfile')
            ->ignore('preset-file.php')
            ->copy();

        $this->assertFileExists($this->tmpPath('source/source-file.md'));
        $this->assertFileMissing($this->tmpPath('preset-file.php'));
        $this->assertFileMissing($this->tmpPath('.dotfile'));
    }

    #[Test]
    public function original_composer_json_is_not_deleted()
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

        (new CustomInstaller)->install($builder)
            ->setup()
            ->delete('composer.json');

        $this->assertFileExists($this->tmpPath('composer.json'));
        $this->assertEquals(
            $old_composer,
            json_decode(file_get_contents($this->tmpPath('composer.json')), true),
        );
    }

    #[Test]
    public function original_composer_json_is_merged_with_new_composer_json_after_copy()
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
                'composer.json' => json_encode([
                    'require' => [
                        'other/package' => '2.0',
                    ],
                ]),
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->copy();

        $this->assertEquals(
            [
                'require' => [
                    'tightenco/jigsaw' => '^1.2',
                    'test/preset' => '1.0',
                    'other/package' => '2.0',
                ],
            ],
            json_decode(file_get_contents($this->tmpPath('composer.json')), true),
        );
    }

    #[Test]
    public function composer_json_files_are_merged_when_copying_multiple_times()
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
                'composer.json' => json_encode([
                    'require' => [
                        'other/package' => '2.0',
                    ],
                ]),
                'theme' => [
                    'composer.json' => json_encode([
                        'require' => [
                            'another/package' => '3.0',
                        ],
                    ]),
                ],
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->copy()
            ->from('theme')
            ->copy();

        $this->assertEquals(
            [
                'require' => [
                    'tightenco/jigsaw' => '^1.2',
                    'test/preset' => '1.0',
                    'other/package' => '2.0',
                    'another/package' => '3.0',
                ],
            ],
            json_decode(file_get_contents($this->tmpPath('composer.json')), true),
        );
    }

    #[Test]
    public function empty_composer_json_is_created_if_it_was_not_present_before_preset_was_installed()
    {
        $this->createSource([
            'package' => [
                'package-file.php' => '',
            ],
        ]);
        $package = Mockery::mock(PresetPackage::class);
        $package->path = $this->tmp . '/package';
        $builder = new PresetScaffoldBuilder(new Filesystem, $package, new ProcessRunner);
        $builder->setBase($this->tmp);

        (new CustomInstaller)->install($builder)
            ->setup()
            ->copy();

        $this->assertEquals(
            [
                'require' => [],
            ],
            json_decode(file_get_contents($this->tmpPath('composer.json')), true),
        );
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function installer_can_ask_for_user_input()
    {
        $console = Mockery::spy(ConsoleSession::class);
        $builder = Mockery::spy(PresetScaffoldBuilder::class);

        (new CustomInstaller)->setConsole($console)
            ->install($builder)
            ->setup()
            ->ask('What is your name?');

        $console->shouldHaveReceived('ask')
            ->with('What is your name?', null, null, null);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function installer_can_ask_for_user_input_with_choices()
    {
        $console = Mockery::spy(ConsoleSession::class);
        $builder = Mockery::spy(PresetScaffoldBuilder::class);

        (new CustomInstaller)->setConsole($console)
            ->install($builder)
            ->setup()
            ->ask(
                'What theme would you like to use?',
                ['l' => 'light', 'd' => 'dark'],
                $default = 'l',
            );

        $console->shouldHaveReceived('ask')
            ->with(
                'What theme would you like to use?',
                ['l' => 'light', 'd' => 'dark'],
                'l',
                null,
            );
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function installer_can_ask_for_user_confirmation()
    {
        $console = Mockery::spy(ConsoleSession::class);
        $builder = Mockery::spy(PresetScaffoldBuilder::class);

        (new CustomInstaller)->setConsole($console)
            ->install($builder)
            ->setup()
            ->confirm('Continue?');

        $console->shouldHaveReceived('confirm')
            ->with('Continue?', null);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function installer_runs_specified_commands_from_init()
    {
        $package = Mockery::mock(PresetPackage::class);
        $builder = Mockery::spy(PresetScaffoldBuilder::class);

        (new CustomInstaller)->setConsole(null)
            ->install($builder)
            ->setup()
            ->run('yarn');

        $builder->shouldHaveReceived('runCommands')->with('yarn');
    }
}
