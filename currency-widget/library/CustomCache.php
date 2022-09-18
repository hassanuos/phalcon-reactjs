<?php

use Phalcon\Cache\Multiple;
use Phalcon\Cache\Backend\Apc as ApcCache;
use Phalcon\Cache\Backend\File as FileCache;
use Phalcon\Cache\Frontend\Data as DataFrontend;
use Phalcon\Cache\Backend\Memcache as MemcacheCache;

class CustomCache{

    public function cacheSettings($lifeTime = 0, $type = 'file'){

        $cacheObj = $this->getCacheType($type, $lifeTime);

        // we can add multiple ini future
        $multipleCache = new Multiple([$cacheObj]);

        return $multipleCache;
    }

    public function getCacheType($type, $lifeTime){
        switch ($type){
            case 'file':
                $slowFrontend = new DataFrontend(
                    [
                        'lifetime' => $lifeTime,
                    ]
                );
                $cacheObj = new FileCache(
                    $slowFrontend,
                    [
                        'prefix'   => 'cache',
                        'cacheDir' => '../cache/',
                    ]
                );
                break;
            case 'mem':
                $fastFrontend = new DataFrontend(
                    [
                        'lifetime' => $lifeTime,
                    ]
                );
                $cacheObj = new MemcacheCache(
                    $fastFrontend,
                    [
                        'prefix' => 'cache',
                        'host'   => 'localhost',
                        'port'   => '11211',
                    ]
                );
                break;
            case 'apc':
                $ultraFastFrontend = new DataFrontend(
                    [
                        'lifetime' => $lifeTime,
                    ]
                );
                $cacheObj = new ApcCache(
                    $ultraFastFrontend,
                    [
                        'prefix' => 'cache',
                    ]
                );
                break;
            default:
                $slowFrontend = new DataFrontend(
                    [
                        'lifetime' => $lifeTime,
                    ]
                );
                $cacheObj = new FileCache(
                    $slowFrontend,
                    [
                        'prefix'   => 'cache',
                        'cacheDir' => '../cache/',
                    ]
                );
        }

        return $cacheObj;
    }
}