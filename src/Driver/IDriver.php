<?php


namespace Edv\Cache\Driver;


interface IDriver
{
    public function cacheKey();
    public function config();

}