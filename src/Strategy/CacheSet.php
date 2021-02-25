<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\IStrategy;
use Edv\Cache\RedisUtil;

abstract class CacheSet extends AbstractContext
{

    protected $expireAt;
    protected $expire;
    protected $client;
    protected $KEY;

    /**
     * CacheList constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (empty($this->KEY))
            throw new \Exception('property KEY must overwrite');

        $this->client   = RedisUtil::client();
    }

    public function get($key = '')
    {
        return $this->client->sMembers($this->KEY);
    }

    public function exec(callable $callback)
    {
        $callback($this->client,$this->KEY);
    }

    public function clean($key = '') : IStrategy
    {
        $this->client->del($this->KEY);
        return $this;
    }

    public function patchSelf(callable $callback , string $key = '')
    {
        if ($this->client->exists($this->KEY))
            return $this->get();

        try {
            $result = $callback();
        }catch (\Exception $exception){
            throw $exception;
        }

        $this->client->pipeline();

        foreach ($result as $item){

            $val = is_array($item) ? json_encode($item) : strval($item);
            $this->client->sAdd($this->KEY,$val);
        }

        $this->client->exec();

        if ($this->expireAt){
            $this->client->expireAt($this->KEY,$this->expireAt);
        }

        if ($this->expire){
            $this->client->expire($this->KEY,$this->expire);
        }

        return $result;
    }

}
