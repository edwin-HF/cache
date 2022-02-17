<?php


namespace Edv\Cache\Driver\Traits;


trait LocalConfig
{

    public function config()
    {
        return [
            'host' => '127.0.0.1',
            'port' => '6379',
            'password' => '',
            'database' => 1
        ];
    }
}