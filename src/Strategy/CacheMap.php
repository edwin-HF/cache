<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;

abstract class CacheMap extends AbstractContext
{

    public static function newInstance($callback = null):self
    {
        return parent::newInstance($callback);
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

    /**
     * @param array $keys
     * @return array
     */
    public function getMultiple($keys = []){

        if (empty($keys)){
            $result = $this->client()->hGetAll($this->cacheKey());
        }else{
            $result = $this->client()->hMGet($this->cacheKey(), $keys);
        }

        $returnData = [];
        foreach ($result as $key => $value){
            $returnData[$keys[$key]] = unserialize($value);
        }

        return $returnData;

    }

    /**
     * @return bool|int
     */
    public function size(){
        return $this->client()->hLen($this->cacheKey());
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function put($key, $value){
        $this->fill([$key => $value]);
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
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

    /**
     * @param $data
     */
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
