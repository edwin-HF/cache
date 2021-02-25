<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\RedisUtil;

abstract class CacheString extends AbstractContext
{

    protected $expireAt;
    protected $expire;
    protected $PREFIX = 'edv:cache:string:';

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

        return unserialize(RedisUtil::client()->get($this->PREFIX . $key));

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

            if (empty($this->PREFIX))
                return $this;

            $iterator = null;

            do{

                $keys = RedisUtil::client()->scan($iterator,$this->PREFIX . '*');

                foreach ($keys as $key){
                    RedisUtil::client()->del($key);
                }

            }while($iterator > 0);
        }else{
            RedisUtil::client()->del($this->PREFIX . $key);
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

        if (RedisUtil::client()->exists($this->PREFIX . $key))
            return $this->get($key);

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        $val = is_array($result) ? json_encode($result) : $result;

        RedisUtil::client()->set($this->PREFIX . $key,serialize($val));

        if ($this->expireAt){
            RedisUtil::client()->expireAt($this->PREFIX . $key,$this->expireAt);
        }

        if ($this->expire){
            RedisUtil::client()->expire($this->PREFIX . $key,$this->expire);
        }

        return $result;

    }

}
