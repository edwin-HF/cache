<?php


namespace Edv\Cache\Driver\Traits;


trait AutoGenerateCacheKey
{

    public function cacheKey()
    {
        return sprintf('edv:cache:key:%s',get_called_class());
    }

}