<?php


namespace Edv\Cache;


use Redis;

class CacheDriver
{

    private static $host = '127.0.0.1';
    private static $port = '6379';
    private static $password = '';
    private static $database = 1;
    private static $client = null;

    /**
     * @return Redis|null
     */
    public static function client(){

        if (self::$client)
            return self::$client;

        self::$client = new Redis();

        self::$client->connect(self::$host,self::$port);
        self::$client->auth(self::$password);
        self::$client->select(self::$database);

        return self::$client;

    }

    public function __destruct()
    {
        self::$client->close();
    }

    /**
     * @param string $host
     */
    public static function setHost(string $host)
    {
        self::$host = $host;
    }

    /**
     * @param string $port
     */
    public static function setPort(string $port)
    {
        self::$port = $port;
    }

    /**
     * @param string $password
     */
    public static function setPassword(string $password)
    {
        self::$password = $password;
    }

    /**
     * @param int $database
     */
    public static function setDatabase(int $database)
    {
        self::$database = $database;
    }

}
