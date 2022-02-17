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

    public static function newInstance(): self
    {
        return parent::newInstance();
    }

    public function forPage($page, $limit){

        $this->begin = ($page - 1) * $limit;
        $this->end   = $this->begin + $limit - 1;

        return $this;
    }

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

    public function size(){
        return $this->client()->zCard($this->cacheKey());
    }

    public function append($data){

        try {
            $this->fill($data);
        }catch (\Exception $exception){}

        return $this;

    }


    protected function fill($data){

        if (!is_array($data) || empty($data))
            return;

        $last = $this->size();

        $this->client()->pipeline();

        $index = 0;
        foreach ($data as $item){
            if($this->client()->zAdd($this->cacheKey(), ['NX'], $index + $last, serialize($item))){
                $index++;
            }
        }

        $this->client()->exec();

    }

}
