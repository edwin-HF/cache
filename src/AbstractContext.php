<?php


namespace Edv\Cache;


/**
 * Class AbstractContext
 * @package Edv\Cache
 */
abstract class AbstractContext implements IStrategy
{

    public static function strategy($config = []): IStrategy
    {

        if (isset($config['host']) && !empty($config['host']))
            RedisUtil::setHost($config['host']);

        if (isset($config['port']) && !empty($config['port']))
            RedisUtil::setPort($config['port']);

        if (isset($config['password']) && !empty($config['password']))
            RedisUtil::setPassword($config['password']);

        if (isset($config['database']) && !empty($config['database']))
            RedisUtil::setDatabase($config['database']);

        $classHandle = (new static());
        $classHandle->patch(RedisUtil::client(),$classHandle->cacheKey());

        return $classHandle;
    }

    public function cacheKey(){

        if ($this->cacheKey){
            return $this->cacheKey;
        }

        return md5('edv:' . get_class($this));

    }

}
