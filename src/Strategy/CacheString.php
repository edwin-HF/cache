<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\CacheDriver;

abstract class CacheString extends AbstractContext
{

    protected $expireAt;
    protected $expire;

    public function exec(callable $callback)
    {
        $callback(CacheDriver::client());
    }

    /**
     * @param mixed $key
     * @return array|mixed|string
     */
    public function get($key = '')
    {
        if (empty($key))
            return  '';

        return unserialize(CacheDriver::client()->get($this->cacheKey() . $key));

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

                $keys = CacheDriver::client()->scan($iterator,$this->cacheKey() . '*');

                foreach ($keys as $key){
                    CacheDriver::client()->del($key);
                }

            }while($iterator > 0);
        }else{
            CacheDriver::client()->del($this->cacheKey() . $key);
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

        if (CacheDriver::client()->exists($this->cacheKey() . $key))
            return $this->get($key);

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        $val = is_array($result) ? json_encode($result) : $result;

        CacheDriver::client()->set($this->cacheKey() . $key,serialize($val));

        if ($this->expireAt){
            CacheDriver::client()->expireAt($this->cacheKey() . $key,$this->expireAt);
        }

        if ($this->expire){
            CacheDriver::client()->expire($this->cacheKey() . $key,$this->expire);
        }

        return $result;

    }

}
