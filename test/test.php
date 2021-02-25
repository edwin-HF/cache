<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Edv\Cache\Strategy\CacheList;

class PatchList extends CacheList
{
//    protected $cacheKey = 'aaaa';

    public function patch(\Redis $client, $cacheKey)
    {

        $client->zAdd($cacheKey,1,2);
        $client->zAdd($cacheKey,3,2);
        $client->zAdd($cacheKey,5,2);
        // TODO: Implement patch() method.
    }
}

try {

//    $res = PatchList::strategy()->get();
//    var_dump($res);

    $res = PatchList::strategy()
//        ->clean()
        ->patchSelf(
        function () {
            return [1, 2, 3,5];
        }
    );
    var_dump($res);
} catch (Exception $e) {
}

