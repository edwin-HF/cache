<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\RedisUtil;

abstract class CacheList extends AbstractContext
{

    protected $cacheKey;
    protected $expireAt;
    protected $expire;

    public function get($key = '')
    {
        return RedisUtil::client()->zRangeByScore($this->cacheKey(),'-inf','+inf');
    }

    public function exec(callable $callback)
    {
        $callback(RedisUtil::client(),$this->cacheKey());
    }

    public function clean($key = '') : IStrategy
    {
        RedisUtil::client()->del($this->cacheKey());
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
        if (RedisUtil::client()->exists($this->cacheKey()))
            return $this->get();

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        RedisUtil::client()->pipeline();

        foreach ($result as $key => $item){

            $val = is_array($item) ? json_encode($item) : strval($item);
            RedisUtil::client()->zAdd($this->cacheKey(),$key,$val);
        }

        RedisUtil::client()->exec();

        if ($this->expireAt){
            RedisUtil::client()->expireAt($this->cacheKey(),$this->expireAt);
        }

        if ($this->expire){
            RedisUtil::client()->expire($this->cacheKey(),$this->expire);
        }

        return $result;
    }

}
