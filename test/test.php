<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/provider/Provider.php';
require_once __DIR__ . '/provider/person/PersonInfo.php';
require_once __DIR__ . '/provider/person/PersonList.php';


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

    public function expire()
    {
        return 60;
    }

    public function patch()
    {
        return [12=>1,23=>1];
    }
}

class Str extends \Edv\Cache\Strategy\CacheString{
    use \Edv\Cache\Driver\Traits\LocalConfig;
    use \Edv\Cache\Driver\Traits\AutoGenerateCacheKey;


    public function expire()
    {
         return 60 * 60;
         //return '2022-02-23 17:20';
    }

}

try {


/*    // bitmap operate
    Bit2::newInstance()->flush();
    Bit1::newInstance()->flush();
    $bt1 = Bit1::newInstance()->resume(2)->resume(3);
    $bt2 = Bit2::newInstance()->resume(2);

    $newbt = $bt1->opAND($bt2);

    var_dump($newbt->status(3));

    // bitmap get set multiple operate

    var_dump(Bit2::newInstance()->resume(2)->status(12));

    Bit2::newInstance()->revokeMultiple([2,12]);

    var_dump(Bit2::newInstance()->status(2));
    var_dump(Bit2::newInstance()->status(12));
    var_dump(Bit2::newInstance()->status(23));

    // List
    $res = PersonList::newInstance()->forPage(2,1)->get();
    var_dump($res);

    // Map
    $res = PersonInfo::newInstance()->get(2);
    var_dump($res);*/

    Str::newInstance()->flush();
    $res = Str::newInstance()->setParam('id',12)->store('bb',function (){

        var_dump(12121212);
        return [1,2,3];
    });

    var_dump($res);

    var_dump(Str::newInstance()->setParam('id',12)->get('bb'));



} catch (Exception $e) {
    var_dump($e->getMessage());
}

