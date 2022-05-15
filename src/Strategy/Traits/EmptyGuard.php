<?php


namespace Edv\Cache\Strategy\Traits;


trait EmptyGuard
{

    public function avalanche(\Closure $callback){
        return $callback();
    }

    public function hotSpot(\Closure $callback){
        return $callback();
    }

    public function penetration(\Closure $callback){
        return $callback();
    }


}