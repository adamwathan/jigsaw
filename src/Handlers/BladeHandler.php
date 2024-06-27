<?php

namespace TightenCo\Jigsaw\Handlers;

use TightenCo\Jigsaw\File\OutputFile;
use TightenCo\Jigsaw\File\TemporaryFilesystem;
use TightenCo\Jigsaw\PageData;
use TightenCo\Jigsaw\Parsers\FrontMatterParser;
use TightenCo\Jigsaw\View\ViewRenderer;

class BladeHandler
{
    private $temporaryFilesystem;

    private $parser;

    private $view;

    private $hasFrontMatter;

    public function __construct(TemporaryFilesystem $temporaryFilesystem, FrontMatterParser $parser, ViewRenderer $viewRenderer)
    {
        $this->temporaryFilesystem = $temporaryFilesystem;
        $this->parser = $parser;
        $this->view = $viewRenderer;
    }

    public function shouldHandle($file)
    {
        // File should have a valid extra blade extension, or be a blade.php file
        return $file->getExtraBladeExtension() != '' || $file->getFullExtension() == 'blade.php';
    }

    public function handleCollectionItem($file, PageData $pageData)
    {
        $this->getPageVariables($file);

        return $this->buildOutput($file, $pageData);
    }

    public function handle($file, $pageData)
    {
        $pageData->page->addVariables($this->getPageVariables($file));

        return $this->buildOutput($file, $pageData);
    }

    private function buildOutput($file, $pageData)
    {
        $extension = strtolower($file->getExtension());

        return collect([
            new OutputFile(
                $file,
                $file->getRelativePath(),
                $file->getFilenameWithoutExtension(),
                $extension == 'php' ? 'html' : $extension,
                $this->hasFrontMatter ?
                    $this->renderWithFrontMatter($file, $pageData) :
                    $this->render($file->getPathName(), $pageData),
                $pageData,
            ),
        ]);
    }

    private function getPageVariables($file)
    {
        $frontMatter = $this->parseFrontMatter($file);
        $this->hasFrontMatter = count($frontMatter) > 0;

        return $frontMatter;
    }

    private function parseFrontMatter($file)
    {
        return $this->parser->getFrontMatter($file->getContents());
    }

    private function render($path, $pageData)
    {
        return $this->view->render($path, $pageData);
    }

    private function renderWithFrontMatter($file, $pageData)
    {
        $bladeFilePath = $this->temporaryFilesystem->put(
            $this->parser->getBladeContent($file->getContents()),
            $file->getPathname(),
            '.blade.php',
        );

        return $this->render($bladeFilePath, $pageData);
    }
}
