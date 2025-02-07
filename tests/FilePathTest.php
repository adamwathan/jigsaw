<?php

namespace Tests;

use TightenCo\Jigsaw\IterableObject;
use TightenCo\Jigsaw\PageVariable;
use TightenCo\Jigsaw\PathResolvers\CollectionPathResolver;
use TightenCo\Jigsaw\PathResolvers\PrettyOutputPathResolver;

class FilePathTest extends TestCase
{
    public function test_accented_character_in_filename_is_replaced_with_unaccented_character_when_using_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Fútbol solidario');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/futbol-solidario', $outputPath[0]);
    }

    public function test_accented_character_in_filename_is_replaced_with_unaccented_character_when_using_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Fútbol solidario');
        $outputPath = $pathResolver->link('{filename}', $pageVariable);

        $this->assertEquals('/Futbol solidario', $outputPath[0]);
    }

    public function test_accented_character_in_filename_is_replaced_with_unaccented_character_when_using_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Fútbol solidario');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable);

        $this->assertEquals('/futbol_solidario', $outputPath[0]);
    }

    public function test_invalid_characters_in_filename_are_removed_when_using_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Has® Invalid™ Characters');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/has-invalid-characters', $outputPath[0]);
    }

    public function test_leading_periods_are_not_removed()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('.well-known');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/.well-known', $outputPath[0]);
    }

    public function test_invalid_characters_in_filename_are_removed_when_using_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Has® Invalid™ Characters');
        $outputPath = $pathResolver->link('{filename}', $pageVariable);

        $this->assertEquals('/Has Invalid Characters', $outputPath[0]);
    }

    public function test_invalid_characters_in_filename_are_removed_when_using_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('Has® Invalid™ Characters');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable);

        $this->assertEquals('/has_invalid_characters', $outputPath[0]);
    }

    // @todo make this consisten in v2
    public function test_some_international_characters_in_filename_are_allowed_when_using_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('테스트-파일-이름');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/테스트-파일-이름', $outputPath[0]);
    }

    // @todo make this consisten in v2
    public function test_some_international_characters_in_filename_are_allowed_when_using_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('테스트-파일-이름');
        $outputPath = $pathResolver->link('{filename}', $pageVariable);

        $this->assertEquals('/테스트-파일-이름', $outputPath[0]);
    }

    // @todo make this consisten in v2
    public function test_some_international_characters_in_filename_are_allowed_when_using_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('테스트-파일-이름');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable);

        $this->assertEquals('/테스트_파일_이름', $outputPath[0]);
    }

    // @todo make this consisten in v2
    public function test_some_international_characters_in_filename_are_not_allowed_when_using_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('اختبار-مسار-الملف');
        $outputPath = $pathResolver->link(null, $pageVariable);

        $this->assertEquals('/akhtbar-msar-almlf', $outputPath[0]);
    }

    // @todo make this consisten in v2
    public function test_some_international_characters_in_filename_are_not_allowed_when_using_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('اختبار-مسار-الملف');
        $outputPath = $pathResolver->link('{filename}', $pageVariable);

        $this->assertEquals('/akhtbar-msar-almlf', $outputPath[0]);
    }

    // @todo make this consisten in v2
    public function test_some_international_characters_in_filename_are_not_allowed_when_using_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('اختبار-مسار-الملف');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable);

        $this->assertEquals('/akhtbar_msar_almlf', $outputPath[0]);
    }

    public function test_disable_transliteration_when_using_default_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('اختبار-مسار-الملف');
        $outputPath = $pathResolver->link(null, $pageVariable, false);

        $this->assertEquals('/اختبار-مسار-الملف', $outputPath[0]);
    }

    public function test_disable_transliteration_when_using_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('اختبار-مسار-الملف');
        $outputPath = $pathResolver->link('{filename}', $pageVariable, false);

        $this->assertEquals('/اختبار-مسار-الملف', $outputPath[0]);
    }

    public function test_disable_transliteration_when_using_slugified_shorthand_path_config()
    {
        $this->app->instance('outputPathResolver', new PrettyOutputPathResolver);
        $pathResolver = $this->app->make(CollectionPathResolver::class);
        $pageVariable = $this->getPageVariableDummy('اختبار-مسار-الملف');
        $outputPath = $pathResolver->link('{_filename}', $pageVariable, false);

        $this->assertEquals('/اختبار_مسار_الملف', $outputPath[0]);
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
