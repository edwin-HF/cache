<?php


namespace Edv\Cache\Strategy\Traits;

/**
 * Trait EmptyGuard
 *
 * @package Edv\Cache\Strategy\Traits
 * 不需要守卫
 */
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