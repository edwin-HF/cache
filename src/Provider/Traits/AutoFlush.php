<?php


namespace Edv\Cache\Provider\Traits;


trait AutoFlush
{

    public function clean()
    {
        $this->client()->del($this->cacheKey());
    }

    public function flush()
    {
        try {

            $iterator = null;

            do{

                $keys = $this->client()->scan($iterator, sprintf('%s*',$this->cacheKeyPrefix()));

                foreach ($keys as $key){
                    $this->client()->del($key);
                }

            }while($iterator > 0);

        }catch (\Exception $exception){}

    }
}