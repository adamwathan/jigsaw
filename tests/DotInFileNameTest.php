<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use TightenCo\Jigsaw\Handlers\BladeHandler;
use TightenCo\Jigsaw\Handlers\MarkdownHandler;
use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\PageVariable;
use TightenCo\Jigsaw\PathResolvers\CollectionPathResolver;
use TightenCo\Jigsaw\PathResolvers\PrettyOutputPathResolver;

class DotInFileNameTest extends TestCase
{
    #[Test]
    public function md_files_with_dot_in_filename_are_processed()
    {
        $inputFile = $this->getInputFile('dot-files/filename-with.dot.1.md');
        $handler = $this->app->make(MarkdownHandler::class);
        $outputFile = $handler->handle($inputFile, $this->getPageDataDummy());

        $this->assertTrue($handler->shouldHandle($inputFile));
        $this->assertEquals('filename-with.dot.1', $outputFile[0]->name());
        $this->assertEquals('Test with dot in filename', $outputFile[0]->data()->page->title);
        $this->assertStringContainsString('<h3>This file contains a dot in the filename</h3>', $outputFile[0]->contents());
    }

    #[Test]
    public function blade_md_hybrid_files_with_dot_in_filename_are_processed()
    {
        $inputFile = $this->getInputFile('dot-files/filename-with.dot.2.blade.md');
        $handler = $this->app->make(MarkdownHandler::class);
        $outputFile = $handler->handle($inputFile, $this->getPageDataDummy());

        $this->assertTrue($handler->shouldHandle($inputFile));
        $this->assertEquals('filename-with.dot.2', $outputFile[0]->name());
        $this->assertEquals('Second test with dot in filename', $outputFile[0]->data()->page->title);
        $this->assertStringContainsString('<h3>This file also contains a dot in the filename</h3>', $outputFile[0]->contents());
    }

    #[Test]
    public function blade_files_with_dot_in_filename_are_processed()
    {
        $inputFile = $this->getInputFile('dot-files/filename-with.dot.3.blade.php');
        $handler = $this->app->make(BladeHandler::class);
        $outputFile = $handler->handle($inputFile, $this->getPageDataDummy());

        $this->assertTrue($handler->shouldHandle($inputFile));
        $this->assertEquals('filename-with.dot.3', $outputFile[0]->name());
        $this->assertEquals('Test with dot in filename', $outputFile[0]->data()->page->title);
        $this->assertStringContainsString('<h3>This file contains a dot in the filename</h3>', $outputFile[0]->contents());
    }

    #[Test]
    public function dot_in_filename_is_preserved_for_collection_item_with_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('collection-item-with.dot');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/collection-item-with.dot', $outputPath[0]);
    }

    #[Test]
    public function dot_in_filename_is_preserved_for_collection_item_with_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('collection-item-with.dot');
        $outputPath = $pathResolver->link('{filename}', $pageVariable);

        $this->assertEquals('/collection-item-with.dot', $outputPath[0]);
    }

    #[Test]
    public function dot_in_filename_is_preserved_for_collection_item_with_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('collection-item-with.dot');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable);

        $this->assertEquals('/collection_item_with.dot', $outputPath[0]);
    }

    protected function getPageDataDummy()
    {
        return PageData::withPageMetaData(new IterableObject, []);
    }

    protected function getPageVariableDummy($filename)
    {
        return new PageVariable([
            'extends' => '_layouts/test-base',
            '_meta' => new IterableObject([
                'collectionName' => '',
                'filename' => $filename,
            ]),
        ]);
    }
}
