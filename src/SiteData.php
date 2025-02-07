<?php

namespace TightenCo\Jigsaw;

use Illuminate\Support\Collection;

class SiteData extends IterableObject
{
    public static function build(Collection $config)
    {
        $siteData = new static;
        $siteData->putIterable('collections', $config->get('collections'));
        $siteData->putIterable('page', $config);

        return $siteData;
    }

    public function addCollectionData(array $collectionData)
    {
        collect($collectionData)->each(function ($collection, $collectionName) {
            return $this->put($collectionName, new PageVariable($collection));
        });

        return $this->forget('collections');
    }
}
