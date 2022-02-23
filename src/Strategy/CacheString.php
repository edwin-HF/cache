<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\Provider\Traits\EmptyPatch;
use Edv\Cache\Strategy\Traits\EmptyFill;

abstract class CacheString extends AbstractContext
{

    use EmptyPatch;
    use EmptyFill;

    public static function newInstance():self
    {
        return parent::newInstance();
    }

    private function packKey(string $key){
        return sprintf('%s:%s', $this->cacheKey(), $key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (empty($key))
            return  '';

        try {
            return unserialize($this->client()->get($this->packKey($key)));
        }catch (\Exception $exception){
            return false;
        }

    }

    public function del(string $key){
        $this->client()->del($this->packKey($key));
        return $this;
    }

    public function forever(string $key, $value){

        if ($this->client()->exists($this->packKey($key)))
            return $this->get($key);

        $cacheData = is_callable($value) ? $value() : $value;

        $this->client()->set($this->packKey($key),serialize($cacheData));

        return $cacheData;
    }

    public function store(string $key, $value){

        if ($this->client()->exists($this->packKey($key)))
            return $this->get($key);

        $cacheData = is_callable($value) ? $value() : $value;

        $this->client()->set($this->packKey($key),serialize($cacheData));
        $this->fillExpire($this->packKey($key));

        return $cacheData;
    }

    public function remember(string $key, $ttl, $value){

        if ($this->client()->exists($this->packKey($key)))
            return $this->get($key);

        $cacheData = is_callable($value) ? $value() : $value;

        $this->client()->setex($this->packKey($key), $ttl, serialize($cacheData));

        return $cacheData;
    }

    public function ttl($key, $duration){
        $this->client()->expire($this->packKey($key),$duration);
        return $this;
    }

    public function ttlAt(string $key, $datetime){
        $this->client()->expireAt($this->packKey($key),$datetime);
        return $this;
    }

    public function set(string $key, $value){
        $this->client()->set($this->packKey($key), serialize($value));
        return $this;
    }

    public function setEX(string $key, $value, $ttl){
        $this->client()->setex($this->packKey($key), $ttl, serialize($value));
        return $this;
    }

    public function setNX(string $key, $value){
        $this->client()->setnx($this->packKey($key), serialize($value));
        return $this;
    }

    public function size(string $key){
        return $this->client()->strlen($this->packKey($key));
    }

    public function inc(string $key){
        return $this->client()->incr($this->packKey($key));
    }

    public function incBy(string $key, $step){
        return $this->client()->incrBy($this->packKey($key), $step);
    }

    public function incByFloat(string $key, $step){
        return $this->client()->incrByFloat($this->packKey($key), $step);
    }

    public function flush()
    {
        try {

            $iterator = null;

            do{

                $keys = $this->client()->scan($iterator, $this->packKey('*'));

                foreach ($keys as $key){
                    $this->client()->del($key);
                }

                }while($iterator > 0);

        }catch (\Exception $exception){}

    }


}
