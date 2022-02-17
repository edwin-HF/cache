<?php


namespace Edv\Cache\Strategy;


use Edv\Cache\AbstractContext;
use Edv\Cache\Provider\Traits\EmptyPatch;
use Edv\Cache\Strategy\Traits\EmptyFill;

abstract class CacheBitmap extends AbstractContext
{

    use EmptyPatch;
    use EmptyFill;

    public static function newInstance():self
    {
        return parent::newInstance();
    }

    public function resume(int $offset){
        $this->client()->setBit($this->cacheKey(),$offset,1);
        return $this;
    }

    public function revoke(int $offset){
        $this->client()->setBit($this->cacheKey(),$offset,0);
        return $this;
    }

    public function status(int $offset){
        return $this->client()->getBit($this->cacheKey(), $offset);
    }

    public function resumeCount(){
        return $this->client()->bitCount($this->cacheKey());
    }

    public function opAND(CacheBitmap $target){
        return $this->op($this->cacheKey(),$target->cacheKey(),'AND');
    }

    public function opOR(CacheBitmap $target){
        return $this->op($this->cacheKey(),$target->cacheKey(),'OR');
    }

    public function opXOR(CacheBitmap $target){
        return $this->op($this->cacheKey(),$target->cacheKey(),'XOR');
    }

    public function opNOT(CacheBitmap $target){
        return $this->op($this->cacheKey(),$target->cacheKey(),'NOT');
    }

    private function op($op1, $op2, $op){

        return (new class($op1, $op2, $op, $this->config()) extends CacheBitmap{

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



}