<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\RedisUtil;

abstract class CacheString extends AbstractContext
{

    protected $expireAt;
    protected $expire;
    protected $client;
    protected $PREFIX = '';

    /**
     * CacheString constructor.
     */
    public function __construct()
    {

        if (empty($this->PREFIX))
            throw new \Exception('property PREFIX must overwrite');

        $this->client  = RedisUtil::client();

    }

    public function exec(callable $callback)
    {
        $callback($this->client);
    }

    /**
     * @param mixed $key
     * @return array|mixed|string
     */
    public function get($key = '')
    {
        if (empty($key))
            return  '';

        return unserialize($this->client->get($this->PREFIX . $key));

    }

    public function clean($key = ''): IStrategy
    {

        if (empty($key)){

            if (empty($this->PREFIX))
                return $this;

            $iterator = null;

            do{

                $keys = $this->client->scan($iterator,$this->PREFIX . '*');

                foreach ($keys as $key){
                    $this->client->del($key);
                }

            }while($iterator > 0);
        }else{
            $this->client->del($this->PREFIX . $key);
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

        if ($this->client->exists($this->PREFIX . $key))
            return $this->get($key);

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        $val = is_array($result) ? json_encode($result) : $result;

        $this->client->set($this->PREFIX . $key,serialize($val));

        if ($this->expireAt){
            $this->client->expireAt($this->PREFIX . $key,$this->expireAt);
        }

        if ($this->expire){
            $this->client->expire($this->PREFIX . $key,$this->expire);
        }

        return $result;

    }

}
