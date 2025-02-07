<?php

namespace Tests;

use Exception;
use Mockery;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use TightenCo\Jigsaw\Scaffold\CustomInstaller;
use TightenCo\Jigsaw\Scaffold\DefaultInstaller;
use TightenCo\Jigsaw\Scaffold\PresetPackage;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\ProcessRunner;

class PresetScaffoldBuilderTest extends TestCase
{
    #[Test]
    public function named_preset_resolves_to_predefined_package_path()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $this->createSource([
            'vendor' => [
                'tightenco' => [
                    'jigsaw-blog-template' => [
                        'init.php' => '',
                    ],
                ],
            ],
        ]);
        $preset->base = $this->tmp;

        $preset->init('blog');

        $this->assertEquals(
            $this->tmp . $this->fixDirectorySlashes('/vendor/tightenco/jigsaw-blog-template'),
            $preset->package->path,
        );
    }

    #[Test]
    public function named_preset_resolves_to_vendor_package_path_if_not_predefined()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $this->createSource([
            'vendor' => [
                'test' => [
                    'package' => [
                        'init.php' => '',
                    ],
                ],
            ],
        ]);
        $preset->base = $this->tmp;

        $preset->init('test/package');

        $this->assertEquals($this->tmp . $this->fixDirectorySlashes('/vendor/test/package'), $preset->package->path);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function package_is_loaded_via_composer_if_not_found_locally()
    {
        $process = Mockery::spy(ProcessRunner::class);
        $this->app->instance(PresetPackage::class, new PresetPackage(new DefaultInstaller, new CustomInstaller, $process));
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $this->createSource(['vendor' => ['test' => ['package' => []]]]);
        $preset->base = $this->tmp;

        $preset->init('test/other-package');

        $process->shouldHaveReceived('run')->with('composer require test/other-package');
    }

    #[Test]
    public function exception_is_thrown_if_package_is_missing_a_slash()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $this->createSource(['vendor' => ['test' => ['package' => []]]]);
        $preset->base = $this->tmp;

        try {
            $preset->init('test');
            $this->fail('Exception not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString(
                "'test' is not a valid package name.",
                $e->getMessage(),
            );
        }
    }

    #[Test]
    public function exception_is_thrown_if_package_init_file_contains_errors()
    {
        $preset = $this->app->make(PresetScaffoldBuilder::class);
        $initFile = '<?php contains-an-error;';
        $this->createSource([
            'vendor' => [
                'test' => [
                    'preset' => [
                        'init.php' => $initFile,
                    ],
                ],
            ],
        ]);
        $preset->base = $this->tmp;

        try {
            $preset->init('test/preset');
            $preset->build();

            $this->fail('Exception not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('contains errors', $e->getMessage());
        }
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function init_file_of_array_type_is_loaded()
    {
        $default_installer = Mockery::spy(DefaultInstaller::class);
        $this->app->instance(DefaultInstaller::class, $default_installer);
        $preset = $this->app->make(PresetScaffoldBuilder::class);

        $initFile = '<?php return [
            "delete" => ["test.json"],
        ];';
        $this->createSource([
            'vendor' => [
                'test' => [
                    'preset' => [
                        'init.php' => $initFile,
                    ],
                ],
            ],
        ]);
        $preset->base = $this->tmp;

        $preset->init('test/preset');
        $preset->build();

        $default_installer->shouldHaveReceived('install')
            ->with($preset, ['delete' => ['test.json']]);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function init_file_of_php_type_is_loaded()
    {
        $custom_installer = Mockery::spy(CustomInstaller::class);
        $this->app->instance(CustomInstaller::class, $custom_installer);
        $preset = $this->app->make(PresetScaffoldBuilder::class);

        $custom_installer->shouldReceive('setConsole')
            ->andReturn($custom_installer)
            ->shouldReceive('install')
            ->with($preset)
            ->andReturn($custom_installer);

        $initFile = '<?php $init->copy("test");';
        $this->createSource([
            'vendor' => [
                'test' => [
                    'preset' => [
                        'init.php' => $initFile,
                    ],
                ],
            ],
        ]);
        $preset->base = $this->tmp;

        $preset->init('test/preset');
        $preset->build();

        $custom_installer->shouldHaveReceived('copy')->with('test');
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function init_file_is_optional()
    {
        $default_installer = Mockery::spy(DefaultInstaller::class);
        $this->app->instance(DefaultInstaller::class, $default_installer);
        $preset = $this->app->make(PresetScaffoldBuilder::class);

        $this->createSource([
            'vendor' => [
                'test' => [
                    'preset' => [],
                ],
            ],
        ]);
        $preset->base = $this->tmp;

        $preset->init('test/preset');
        $preset->build();

        $default_installer->shouldHaveReceived('install')->with($preset, []);
    }

    #[Test]
    public function jigsaw_package_dependency_is_restored_to_fresh_composer_dot_json_when_archiving_site()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $existing_site = [
            'composer.json' => json_encode($old_composer),
            'vendor' => [
                'test' => [
                    'preset' => [],
                ],
            ],
        ];
        $this->createSource($existing_site);
        $preset = $this->app->make(PresetScaffoldBuilder::class)
            ->setBase($this->tmp)
            ->init('test/preset');

        $preset->archiveExistingSite();

        $this->assertEquals(
            [
                'require' => [
                    'tightenco/jigsaw' => '^1.2',
                ],
            ],
            json_decode(file_get_contents($this->tmpPath('composer.json')), true),
        );
    }

    #[Test]
    public function jigsaw__package_dependency_is_restored_to_fresh_composer_dot_json_when_deleting_site()
    {
        $old_composer = [
            'require' => [
                'tightenco/jigsaw' => '^1.2',
                'test/preset' => '1.0',
            ],
        ];
        $existing_site = [
            'composer.json' => json_encode($old_composer),
            'vendor' => [
                'test' => [
                    'preset' => [],
                ],
            ],
        ];
        $this->createSource($existing_site);
        $preset = $this->app->make(PresetScaffoldBuilder::class)
            ->setBase($this->tmp)
            ->init('test/preset');

        $preset->deleteExistingSite();

        $this->assertEquals(
            [
                'require' => [
                    'tightenco/jigsaw' => '^1.2',
                ],
            ],
            json_decode(file_get_contents($this->tmpPath('composer.json')), true),
        );
    }
}
