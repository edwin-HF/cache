<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Edv\Cache\Strategy\CacheList;


/**
 * Class PatchList
 */

class PatchList extends \Edv\Cache\Strategy\CacheString
{

    use \Edv\Cache\Driver\Traits\AutoGenerateCacheKey;
    use \Edv\Cache\Driver\Traits\LocalConfig;


}

try {

    $res = PatchList::newInstance()
        ->put('aa',1)
        ->put('bb',2)
        ->inc('cc');

    var_dump($res);

} catch (Exception $e) {
    var_dump($e->getMessage());
}

