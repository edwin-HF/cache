<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\RedisUtil;

abstract class CacheMap extends AbstractContext
{

    protected $cacheKey;
    protected $expireAt;
    protected $expire;

    public function exec(callable $callback)
    {
        $callback(RedisUtil::client(),$this->cacheKey());
    }

    /**
     * @param mixed $key
     * @return array|mixed|string
     */
    public function get($key = '')
    {
        if (empty($key))
            return RedisUtil::client()->hGetAll($this->cacheKey());

        if (is_array($key)){
            return RedisUtil::client()->hMGet($this->cacheKey(), $key);
        }else{
            return RedisUtil::client()->hGet($this->cacheKey(),$key);
        }

    }

    public function expire($time): IStrategy
    {
        $this->expire = $time;
        return $this;
    }

    public function expireAt($datetime): IStrategy
    {
        $this->expireAt = strtotime($datetime);
        return $this;
    }

    public function clean($key = ''): IStrategy
    {
        if (!empty($key)){
            if (is_array($key)){
                RedisUtil::client()->hDel($this->cacheKey(),...$key);
            }else{
                RedisUtil::client()->hDel($this->cacheKey(),$key);
            }
        }else{
            RedisUtil::client()->del($this->cacheKey());
        }

        return $this;
    }

    /**
     * @param callable $callback
     * @param string $key
     * @return mixed|void
     * @throws \Exception
     */
    public function patchSelf(callable $callback , string $key = '')
    {

        if (!empty($key) && RedisUtil::client()->hExists($this->cacheKey(),$key))
            return json_decode($this->get($key),true);

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        try {

            RedisUtil::client()->hSet($this->cacheKey(),$key,json_encode($result));

            if ($this->expireAt){
                RedisUtil::client()->expireAt($this->cacheKey(),$this->expireAt);
            }

            if ($this->expire){
                RedisUtil::client()->expire($this->cacheKey(),$this->expire);
            }

        }catch (\Exception $exception){}

        return $result;

    }

}
