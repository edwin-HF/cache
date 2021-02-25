<?php


namespace Edv\Cache;


/**
 * 目前使用 Cache-Aside 策略
 * Interface IStrategy
 * @package Edv\Cache
 */
interface IStrategy
{

    /**
     * 自己处理数据
     * @param callable $callback
     * @return mixed
     */
    public function exec(callable $callback);

    /**
     * 获取全部数据
     * @param array|string $key list set 不传
     * @return mixed
     */
    public function get($key = '');

    /**
     * 缓存清理
     * @param array|string $key list set 不传
     * @return mixed
     */
    public function clean($key = '') : self;

    /**
     * @param callable $callback
     * @param string $key list set 不传
     * @return mixed
     * @throws \Exception
     */
    public function patchSelf(callable $callback,string $key = '');

    /**
     * @param \Redis $client
     * @param $cacheKey
     * @return mixed
     */
    public function patch(\Redis $client,$cacheKey);

    /**
     * @param $time
     * @return $this
     */
    public function expire($time) : self ;

    /**
     * @param $datetime
     * @return $this
     */
    public function expireAt($datetime) : self ;



}
