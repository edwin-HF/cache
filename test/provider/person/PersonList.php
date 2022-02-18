<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../Provider.php';

class PersonList extends \Edv\Cache\Strategy\CacheList
{

    use Provider;

    public function patch()
    {
        return [
            [
                'id'   => 1,
                'name' => 'zhangsan'
            ],
            [
                'id'   => 2,
                'name' => 'lisi'
            ]
        ];
    }

}