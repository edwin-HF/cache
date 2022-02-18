<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Edv\Cache\Driver\Traits\LocalConfig;

trait Provider
{

    use LocalConfig;
    use \Edv\Cache\Driver\Traits\AutoGenerateCacheKey;


}