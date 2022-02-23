<?php


namespace Edv\Cache\Driver\Traits;


trait AutoGenerateCacheKey
{

    public function cacheKey()
    {
        return sprintf('edv:cache:key:%s',str_replace('\\','.',get_called_class()));
    }

}