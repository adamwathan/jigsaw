<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TightenCo\Jigsaw\Console\InitCommand;
use TightenCo\Jigsaw\Scaffold\BasicScaffoldBuilder;
use TightenCo\Jigsaw\Scaffold\PresetScaffoldBuilder;

class InitCommandTest extends TestCase
{
    #[Test]
    public function init_command_with_no_arguments_uses_basic_scaffold_for_site()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')
            ->andReturn($basic_scaffold)
            ->shouldReceive('setConsole')
            ->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $this->createSource([]);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->execute([]);

        $this->assertEquals('', $console->getInput()->getArgument('preset'));
        $this->assertStringContainsString('initialized', $console->getDisplay());
    }

    #[Test]
    public function init_command_with_argument_uses_preset_scaffold_for_site()
    {
        $preset_scaffold = Mockery::spy(PresetScaffoldBuilder::class);
        $preset_scaffold->shouldReceive('setBase')
            ->andReturn($preset_scaffold)
            ->shouldReceive('setConsole')
            ->andReturn($preset_scaffold);
        $this->app->instance(PresetScaffoldBuilder::class, $preset_scaffold);

        $this->createSource([]);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->execute(['preset' => 'blog']);

        $preset_scaffold->shouldHaveReceived('init')->with('blog');
        $this->assertEquals('blog', $console->getInput()->getArgument('preset'));
        $this->assertStringContainsString('initialized', $console->getDisplay());
    }

    #[Test]
    public function init_command_displays_error_if_preset_name_is_invalid()
    {
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $console = new CommandTester($command);
        $console->execute(['preset' => 'invalid']);

        $this->assertStringContainsString("'invalid' is not a valid package name.", $console->getDisplay());
    }

    #[Test]
    public function init_command_displays_warning_if_source_directory_exists()
    {
        $this->createSource(['source' => []]);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->setInputs(['c']);
        $console->execute([]);

        $this->assertStringContainsString("It looks like you've already run 'jigsaw init' on this project", $console->getDisplay());
    }

    #[Test]
    public function init_command_displays_warning_if_config_dot_php_exists()
    {
        $this->createSource(['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->setInputs(['c']);
        $console->execute([]);

        $this->assertStringContainsString("It looks like you've already run 'jigsaw init' on this project", $console->getDisplay());
    }

    #[Test]
    public function will_not_build_scaffold_if_site_already_initialized_and_user_chooses_cancel()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')
            ->andReturn($basic_scaffold)
            ->shouldReceive('setConsole')
            ->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $this->createSource(['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->setInputs(['c']);
        $console->execute([]);

        $basic_scaffold->shouldNotHaveReceived('archiveExistingSite');
        $basic_scaffold->shouldNotHaveReceived('build');
        $this->assertStringNotContainsString('initialized', $console->getDisplay());
    }

    #[Test]
    public function will_archive_existing_site_if_user_chooses_archive_option()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')
            ->andReturn($basic_scaffold)
            ->shouldReceive('setConsole')
            ->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $this->createSource(['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->setInputs(['a']);
        $console->execute([]);

        $basic_scaffold->shouldHaveReceived('archiveExistingSite');
        $this->assertStringContainsString('initialized', $console->getDisplay());
    }

    #[Test]
    public function will_build_scaffold_if_site_already_initialized_and_user_chooses_archive()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')
            ->andReturn($basic_scaffold)
            ->shouldReceive('setConsole')
            ->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $this->createSource(['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->setInputs(['a']);
        $console->execute([]);

        $basic_scaffold->shouldHaveReceived('build');
        $this->assertStringContainsString('initialized', $console->getDisplay());
    }

    #[Test]
    public function will_delete_existing_site_if_user_chooses_delete_option()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')
            ->andReturn($basic_scaffold)
            ->shouldReceive('setConsole')
            ->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $this->createSource(['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->setInputs(['d', 'y']);
        $console->execute([]);

        $basic_scaffold->shouldHaveReceived('deleteExistingSite');
        $this->assertStringContainsString('initialized', $console->getDisplay());
    }

    #[Test]
    public function will_build_scaffold_if_site_already_initialized_and_user_chooses_delete()
    {
        $basic_scaffold = Mockery::spy(BasicScaffoldBuilder::class);
        $basic_scaffold->shouldReceive('setBase')
            ->andReturn($basic_scaffold)
            ->shouldReceive('setConsole')
            ->andReturn($basic_scaffold);
        $this->app->instance(BasicScaffoldBuilder::class, $basic_scaffold);

        $this->createSource(['config.php' => '']);
        $command = $this->app->make(InitCommand::class);
        $command->setApplication(new Application);
        $command->setBase($this->tmp);

        $console = new CommandTester($command);
        $console->setInputs(['d', 'y']);
        $console->execute([]);

        $basic_scaffold->shouldHaveReceived('build');
        $this->assertStringContainsString('initialized', $console->getDisplay());
    }
}
