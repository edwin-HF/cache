<?php


namespace Edv\Cache\Provider;


interface IReader
{

    public function patch();
    public function expire();

}