<?php


namespace Edv\Cache\Provider;


interface IWriter
{
    public function flush();
    public function clean();
}