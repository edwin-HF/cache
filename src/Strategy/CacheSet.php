<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\CacheDriver;

abstract class CacheSet extends AbstractContext
{

    protected $cacheKey;
    protected $expireAt;
    protected $expire;

    public function get($key = '')
    {
        try {
            return CacheDriver::client()->sMembers($this->cacheKey());
        }catch (\Exception $exception){
            return [];
        }

    }

    public function exec(callable $callback)
    {
        $callback(CacheDriver::client(),$this->cacheKey());
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

    public function clean($key = '') : IStrategy
    {
        try {
            CacheDriver::client()->del($this->cacheKey());
        }catch (\Exception $exception){}

        return $this;
    }

    public function patchSelf(callable $callback , string $key = '')
    {
        try {

            if (CacheDriver::client()->exists($this->cacheKey()))
                return $this->get();

        }catch (\Exception $exception){}

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        try {

            CacheDriver::client()->pipeline();

            foreach ($result as $item){

                $val = is_array($item) ? json_encode($item) : strval($item);
                CacheDriver::client()->sAdd($this->cacheKey(),$val);
            }

            CacheDriver::client()->exec();

            if ($this->expireAt){
                CacheDriver::client()->expireAt($this->cacheKey(),$this->expireAt);
            }

            if ($this->expire){
                CacheDriver::client()->expire($this->cacheKey(),$this->expire);
            }

        }catch (\Exception $exception){}


        return $result;
    }

}
