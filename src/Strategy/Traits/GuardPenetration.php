<?php


namespace Edv\Cache\Strategy\Traits;


/**
 * Trait GuardPenetration
 * @package Edv\Cache\Strategy\Traits
 * 缓存穿透
 */
trait GuardPenetration
{

     public function penetration(\Closure $callback){
         $result = $callback();
         if (empty($result)){
             $this->client()->setex($this->cacheKey(), 60 * 5, 0);
         }
         return $result;
     }

}