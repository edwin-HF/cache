<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;

abstract class CacheMap extends AbstractContext
{

    public static function newInstance(): self
    {
        return parent::newInstance();
    }

    /**
     * @param $key
     * @return string
     */
    public function get($key)
    {
        try {

            $result = $this->client()->hGet($this->cacheKey(),$key);
            return unserialize($result);

        }catch (\Exception $exception){
            return '';
        }

    }

    public function getMultiple($keys = []){

        if (empty($keys)){
            $result = $this->client()->hGetAll($this->cacheKey());
        }else{
            $result = $this->client()->hMGet($this->cacheKey(), $keys);
        }

        foreach ($result as $key => $value){
            $result[$key] = unserialize($value);
        }

        return $result;

    }

    public function size(){
        return $this->client()->hLen($this->cacheKey());
    }

    public function put($key, $value){
        $this->fill([$key => $value]);
        return $this;
    }

    public function putMultiple($data){
        $this->fill($data);
        return $this;
    }

    /**
     * @param string|array $key
     * @return $this
     */
    public function del($key){
        $this->client()->hDel($this->cacheKey(),$key);
        return $this;
    }

    protected function fill($data){

        if (!is_array($data) || empty($data))
            return;

        $this->client()->pipeline();

        foreach ($data as $key => $item){
            $this->client()->hSet($this->cacheKey(),$key,serialize($item));
        }

        $this->client()->exec();

    }

}
