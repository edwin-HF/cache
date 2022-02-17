<?php


namespace Edv\Cache\Provider\Traits;


trait AutoFlush
{

    public function flush()
    {
        $this->client()->del($this->cacheKey());
    }
}