<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\CacheDriver;

abstract class CacheList extends AbstractContext
{

    protected $cacheKey;
    protected $expireAt;
    protected $expire;

    public function get($key = '')
    {
        return CacheDriver::client()->zRangeByScore($this->cacheKey(),'-inf','+inf');
    }

    public function exec(callable $callback)
    {
        $callback(CacheDriver::client(),$this->cacheKey());
    }

    public function clean($key = '') : IStrategy
    {
        CacheDriver::client()->del($this->cacheKey());
        return $this;
    }

    public function expire($time): IStrategy
    {
        $this->expire =$time;
        return $this;
    }

    public function expireAt($datetime): IStrategy
    {
        $this->expireAt = strtotime($datetime);
        return $this;
    }

    public function patchSelf(callable $callback , string $key = '')
    {
        if (CacheDriver::client()->exists($this->cacheKey()))
            return $this->get();

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        CacheDriver::client()->pipeline();

        foreach ($result as $key => $item){

            $val = is_array($item) ? json_encode($item) : strval($item);
            CacheDriver::client()->zAdd($this->cacheKey(),$key,$val);
        }

        CacheDriver::client()->exec();

        if ($this->expireAt){
            CacheDriver::client()->expireAt($this->cacheKey(),$this->expireAt);
        }

        if ($this->expire){
            CacheDriver::client()->expire($this->cacheKey(),$this->expire);
        }

        return $result;
    }

}
