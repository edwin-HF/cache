<?php


namespace Edv\Cache\Provider\Traits;

trait Forever
{

    public function expire(){
        var_dump(1212);
        return null;
    }
}