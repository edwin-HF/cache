<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;


/**
 * Class CacheList
 * @package Edv\Cache\Strategy
 */
abstract class CacheList extends AbstractContext
{

    private $begin = '-inf';
    private $end   = '+inf';

    public static function newInstance($callback = null):self
    {
        return parent::newInstance($callback);
    }

    /**
     * @param $page
     * @param $limit
     * @return $this
     */
    public function forPage($page, $limit){

        $this->begin = ($page - 1) * $limit;
        $this->end   = $this->begin + $limit - 1;

        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {

        $returnData = [];

        try {
            $result = $this->client()->zRangeByScore($this->cacheKey(),$this->begin,$this->end);
            foreach ($result as $item){
                $returnData[] = unserialize($item);
            }
        }catch (\Exception $exception){}

        return $returnData;
    }

    /**
     * @return int
     */
    public function size(){
        return $this->client()->zCard($this->cacheKey());
    }

    /**
     * @param $data
     * @return $this
     */
    public function append($data){

        try {
            $this->fill($data);
        }catch (\Exception $exception){}

        return $this;

    }


    /**
     * @param $data
     */
    protected function fill($data){

        if (!is_array($data) || empty($data))
            return;

        $last = $this->size();

        $pipeline = $this->client()->pipeline();

        $index = 0;
        foreach ($data as $item){
            if($this->client()->zadd($this->cacheKey(), $index + $last, serialize($item))){
                $index++;
            }
        }

        $pipeline->exec();

    }

}
