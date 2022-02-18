<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../Provider.php';


class PersonInfo extends \Edv\Cache\Strategy\CacheMap
{

    use Provider;


    public function patch()
    {
        return [
            '1' => [
                'id'   => 1,
                'name' => 'zhangsan'
            ],
            '2' =>[
                'id'   => 2,
                'name' => 'lisi'
            ]
        ];
    }
}