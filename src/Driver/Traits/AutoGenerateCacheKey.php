<?php


namespace Edv\Cache\Driver\Traits;


trait AutoGenerateCacheKey
{

    public function cacheKey()
    {
        return $this->cacheKeyPrefix();
    }

}