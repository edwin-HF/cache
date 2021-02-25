<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\RedisUtil;

abstract class CacheString extends AbstractContext
{

    protected $expireAt;
    protected $expire;

    public function exec(callable $callback)
    {
        $callback(RedisUtil::client());
    }

    /**
     * @param mixed $key
     * @return array|mixed|string
     */
    public function get($key = '')
    {
        if (empty($key))
            return  '';

        return unserialize(RedisUtil::client()->get($this->cacheKey() . $key));

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

    public function clean($key = ''): IStrategy
    {

        if (empty($key)){

            if (empty($this->cacheKey()))
                return $this;

            $iterator = null;

            do{

                $keys = RedisUtil::client()->scan($iterator,$this->cacheKey() . '*');

                foreach ($keys as $key){
                    RedisUtil::client()->del($key);
                }

            }while($iterator > 0);
        }else{
            RedisUtil::client()->del($this->cacheKey() . $key);
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

        if (RedisUtil::client()->exists($this->cacheKey() . $key))
            return $this->get($key);

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        $val = is_array($result) ? json_encode($result) : $result;

        RedisUtil::client()->set($this->cacheKey() . $key,serialize($val));

        if ($this->expireAt){
            RedisUtil::client()->expireAt($this->cacheKey() . $key,$this->expireAt);
        }

        if ($this->expire){
            RedisUtil::client()->expire($this->cacheKey() . $key,$this->expire);
        }

        return $result;

    }

}
