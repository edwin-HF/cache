<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Edv\Cache\Strategy\CacheList;

class PatchList extends CacheList
{
    protected $KEY = 'aaaa';

    public function __construct()
    {
        parent::__construct();
        $this->expireAt = strtotime(date('Y-m-d 23:59:59'));
    }

    public function exec(callable $callback)
    {
        $callback($this->client,$this->KEY,$this);
    }


    public function patch()
    {

        if ($this->client->exists($this->KEY))
            return true;

    }

}

$res = PatchList::strategy()->patchSelf(function (){
    return [1,2,3];
});

var_dump($res);