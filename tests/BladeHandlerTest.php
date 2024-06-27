<?php

namespace Tests;

use SplFileInfo;
use TightenCo\Jigsaw\File\InputFile;
use TightenCo\Jigsaw\Handlers\BladeHandler;

class BladeHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function blade_files_with_invalid_extension_are_not_handled()
    {
        $inputFile = new InputFile(new SplFileInfo('foo.blade.swp'));
        $handler = $this->app->make(BladeHandler::class);

        $this->assertFalse($handler->shouldHandle($inputFile));
    }

    /**
     * @test
     */
    public function blade_files_with_valid_extensions_are_handled()
    {
        $inputFile = new InputFile(new SplFileInfo('foo.blade.txt'));
        $handler = $this->app->make(BladeHandler::class);

        $this->assertTrue($handler->shouldHandle($inputFile));
    }

    /**
     * @test
     */
    public function blade_files_with_php_extensions_are_handled()
    {
        $inputFile = new InputFile(new SplFileInfo('foo.blade.php'));
        $handler = $this->app->make(BladeHandler::class);

        $this->assertTrue($handler->shouldHandle($inputFile));
    }
}
