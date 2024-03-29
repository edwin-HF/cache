<?php


namespace Edv\Cache;


use Edv\Cache\Driver\IDriver;
use Edv\Cache\Provider\IReader;
use Edv\Cache\Provider\IWriter;
use Edv\Cache\Provider\Traits\AutoFlush;
use Edv\Cache\Provider\Traits\Forever;
use Edv\Cache\Strategy\Traits\EmptyGuard;
use Predis\Client;
use Redis;

/**
 * Class AbstractContext
 * @package Edv\Cache
 */
abstract class AbstractContext implements IDriver, IReader, IWriter
{

    use AutoFlush;
    use Forever;
    use EmptyGuard;

    protected $params = [];

    public function __destruct()
    {
        try {
            $this->fillExpire($this->cacheKey());
        }catch (\Throwable $exception){}
    }

    protected function client(){

        static $client = null;

        if ($client)
            return $client;

        $config = array_merge(
            [
                'host' => '127.0.0.1',
                'port' => '6379',
                'password' => '',
                'database' => 1
            ],$this->config()
        );

        $client = new Redis();

        $client->connect($config['host'],$config['port']);
        $client->auth($config['password']);
        $client->select($config['database']);

        return $client;

    }

    /**
     * @param callable $callback
     */
    public function exec(callable $callback)
    {
        return $callback($this->client(),$this->cacheKey());
    }

    public static function newInstance($callback){
        $instance = new static();
        try {
            if ($callback){
                $callback($instance);
            }
            $instance->load();
        } catch (\Exception $e) {
        }
        return $instance;
    }

    public function load()
    {

        if ($this->client()->exists($this->cacheKey()))
            return true;

        try {

            $result = $this->penetration(function (){
                 return $this->patch();
            });

        }catch (\Exception $exception){
            throw $exception;
        }

        try {

            if (!empty($result)){
                $this->fill($result);
            }

        }catch (\Exception $exception){}

        return $result;
    }

    protected function fillExpire($key){

        if ($this->client()->ttl($key) > 0)
            return;

        $ttl = $this->expire();

        if (!empty($ttl) && $this->client()->exists($key)){

            if (preg_match('/^\d+$/',$ttl)){
                $this->client()->expire($key, $ttl);
            }elseif(preg_match('/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|[12][0-3])\:(0?[0-9]|[1-5][1-9])\:(0?[0-9]|[1-5][1-9]))?/',$ttl)){
                $this->client()->expireAt($key, strtotime($ttl));
            }
        }
    }

    public function setParam($key, $value){
        $this->params[$key] = $value;
        return $this;
    }

    public function setParams($params){
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function param($key){
        return $this->params[$key] ?? '';
    }

    public function params(){
        return $this->params;
    }

    public function cacheKeyPrefix(){
        return sprintf('edv:cache:key:%s',str_replace('\\','.',get_called_class()));
    }

    abstract protected function fill($data);

}
