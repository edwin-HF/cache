<?php


namespace Edv\Cache;


use Edv\Cache\Driver\IDriver;
use Edv\Cache\Provider\IReader;
use Edv\Cache\Provider\IWriter;
use Edv\Cache\Provider\Traits\AutoFlush;
use mysql_xdevapi\Statement;
use Redis;

/**
 * Class AbstractContext
 * @package Edv\Cache
 */
abstract class AbstractContext implements IDriver, IReader, IWriter
{

    use AutoFlush;

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

    public function exec(callable $callback)
    {
        $callback($this->client(),$this->cacheKey());
    }

    public static function newInstance(){
        $instance = new static();
        try {
            $instance->load();
        } catch (\Exception $e) {
        }
        return $instance;
    }

    protected function load()
    {

        if ($this->client()->exists($this->cacheKey()))
            return true;

        try {
            $result = $this->patch();
        }catch (\Exception $exception){
            throw $exception;
        }

        try {

            $this->fill($result);

        }catch (\Exception $exception){}

        return $result;
    }

    abstract protected function fill($data);

}
