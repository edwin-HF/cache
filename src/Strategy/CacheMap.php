<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\RedisUtil;

abstract class CacheMap extends AbstractContext
{

    protected $expireAt;
    protected $expire;
    protected $client;
    protected $KEY;

    /**
     * CacheMap constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (empty($this->KEY))
            throw new \Exception('property KEY must overwrite');

        $this->client = RedisUtil::client();
    }

    public function exec(callable $callback)
    {
        $callback($this->client,$this->KEY);
    }

    /**
     * @param mixed $key
     * @return array|mixed|string
     */
    public function get($key = '')
    {
        if (empty($key))
            return $this->client->hGetAll($this->KEY);

        if (is_array($key)){
            return $this->client->hMGet($this->KEY, $key);
        }else{
            return $this->client->hGet($this->KEY,$key);
        }

    }

    public function clean($key = ''): IStrategy
    {
        if (!empty($key)){
            if (is_array($key)){
                $this->client->hDel($this->KEY,...$key);
            }else{
                $this->client->hDel($this->KEY,$key);
            }
        }else{
            $this->client->del($this->KEY);
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

        if (!empty($key) && $this->client->hExists($this->KEY,$key))
            return json_decode($this->get($key),true);

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        try {

            $this->client->hSet($this->KEY,$key,json_encode($result));

            if ($this->expireAt){
                $this->client->expireAt($this->KEY,$this->expireAt);
            }

            if ($this->expire){
                $this->client->expire($this->KEY,$this->expire);
            }

        }catch (\Exception $exception){}

        return $result;

    }

}
