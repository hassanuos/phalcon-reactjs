<?php

use GuzzleHttp\Client;
use GuzzlePackage\GuzzleRequest as Request;

class ExchangeGuzzle
{
    protected static $client;

    public static function __callStatic($method, $args)
    {
        return Request::new(static::client())->{$method}(...$args);
    }

    public static function client()
    {
        return static::$client ?: static::$client = new Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }
}
