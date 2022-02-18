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

class Bit1 extends \Edv\Cache\Strategy\CacheBitmap{
    use \Edv\Cache\Driver\Traits\AutoGenerateCacheKey;
    use \Edv\Cache\Driver\Traits\LocalConfig;
    use \Edv\Cache\Provider\Traits\EmptyPatch;

    public function expire()
    {
        // return 60;
        return '2022-02-17 17:46:00';
    }

}

class Bit2 extends \Edv\Cache\Strategy\CacheBitmap{
    use \Edv\Cache\Driver\Traits\AutoGenerateCacheKey;
    use \Edv\Cache\Driver\Traits\LocalConfig;

    public function patch()
    {
        return [12=>1,23=>1];
    }
}

try {


/*    $bt1 = Bit1::newInstance()->resume(2)->resume(3);
    $bt2 = Bit2::newInstance()->resume(2);

    $newbt = $bt1->opAND($bt2);

    var_dump($newbt->status(3));*/

    Bit2::newInstance()->flush();
    var_dump(Bit2::newInstance()->resume(2)->status(12));

    var_dump(Bit2::newInstance()->revokeMultiple([2,12]));

    var_dump(Bit2::newInstance()->status(2));
    var_dump(Bit2::newInstance()->status(12));
    var_dump(Bit2::newInstance()->status(23));




} catch (Exception $e) {
    var_dump($e->getMessage());
}

