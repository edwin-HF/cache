<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\Provider\Traits\EmptyPatch;

abstract class CacheBitmap extends AbstractContext
{

    public static function newInstance($callback = null):self
    {
        return parent::newInstance($callback);
    }

    public function get(){

        $result = [];

        $bitmapStr = $this->client()->get($this->cacheKey());

        if ($bitmap = unpack("C*", $bitmapStr)){
            foreach ($bitmap as $key => $number){
                $offset = ($key - 1) * 8;
                if($number){
                    for ($i = 0; $i < 8; $i++){
                        if (($number >> $i & 1) == 1){
                            $result[] = $offset + (7 - $i);
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function resume(int $offset){
        $this->client()->setBit($this->cacheKey(),$offset,1);
        return $this;
    }

    /**
     * @param array $offset
     * @return $this
     */
    public function resumeMultiple(array $offset){
        $this->fill(array_fill_keys($offset,1));
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function revoke(int $offset){
        $this->client()->setBit($this->cacheKey(),$offset,0);
        return $this;
    }

    /**
     * @param array $offset
     * @return $this
     */
    public function revokeMultiple(array $offset){
        $this->fill(array_fill_keys($offset,0));
        return $this;
    }

    /**
     * @param int $offset
     * @return int
     */
    public function status(int $offset){
        return $this->client()->getBit($this->cacheKey(), $offset);
    }

    /**
     * @return int
     */
    public function resumeCount(){
        return $this->client()->bitCount($this->cacheKey());
    }

    /**
     * @param CacheBitmap $target
     * @return CacheBitmap|mixed
     */
    public function opAND(CacheBitmap $target){
        return $this->op($this->cacheKey(),$target->cacheKey(),'AND');
    }


    /**
     * @param CacheBitmap $target
     * @return CacheBitmap|mixed
     */
    public function opOR(CacheBitmap $target){
        return $this->op($this->cacheKey(),$target->cacheKey(),'OR');
    }

    /**
     * @param CacheBitmap $target
     * @return CacheBitmap|mixed
     */
    public function opXOR(CacheBitmap $target){
        return $this->op($this->cacheKey(),$target->cacheKey(),'XOR');
    }

    /**
     * @param CacheBitmap $target
     * @return CacheBitmap|mixed
     */
    public function opNOT(CacheBitmap $target){
        return $this->op($this->cacheKey(),$target->cacheKey(),'NOT');
    }

    private function op($op1, $op2, $op){

        return (new class($op1, $op2, $op, $this->config()) extends CacheBitmap{

            use EmptyPatch;

            private $op1;
            private $op2;
            private $op;
            private $config;

            public function __construct($op1, $op2, $op, $config)
            {
                $this->op1 = $op1;
                $this->op2 = $op2;
                $this->op  = $op;
                $this->config = $config;
                $this->client()->bitOp($op,$this->cacheKey(),$op1, $op2);
                $this->client()->expire($this->cacheKey(),24 * 3600);
            }

            public function cacheKey()
            {
                return sprintf('%s:%s:%s',$this->op1, $this->op, $this->op2);
            }

            public function config()
            {
                return $this->config;
            }

            public function __destruct()
            {
                $this->client()->del($this->cacheKey());
            }
        });
    }

    /**
     * @param $data
     */
    protected function fill($data)
    {
        if (!is_array($data) || empty($data))
            return;

        $this->client()->pipeline();

        foreach ($data as $key => $item){
            $this->client()->setBit($this->cacheKey(),$key,($item > 0 ? 1 : 0));
        }

        $this->client()->exec();
    }


}