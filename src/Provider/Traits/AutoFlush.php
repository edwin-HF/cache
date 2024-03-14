<?php


namespace Edv\Cache\Provider\Traits;


use Predis\Collection\Iterator\Keyspace;

trait AutoFlush
{

    public function clean()
    {
        $this->client()->del($this->cacheKey());
    }

    public function flush()
    {
        try {
            foreach (new Keyspace($this->client(), sprintf('%s*',$this->cacheKeyPrefix())) as $key) {
                $this->client()->del($key);
            }

        }catch (\Exception $exception){}

    }
}