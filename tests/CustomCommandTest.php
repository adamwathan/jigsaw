<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TightenCo\Jigsaw\Console\Command;

class CustomCommandTest extends TestCase
{
    #[Test]
    public function custom_command_with_no_arguments()
    {
        $this->createSource([]);
        $command = $this->app->make(CustomCommand::class);
        $command->setApplication(new Application);

        $console = new CommandTester($command);
        $console->execute([]);

        $this->assertStringContainsString('Command Tested', $console->getDisplay());
    }
}

class CustomCommand extends Command
{
    protected function fire()
    {
        $this->console->info('Command Tested');
    }
}
