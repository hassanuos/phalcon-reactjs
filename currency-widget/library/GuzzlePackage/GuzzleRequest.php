<?php

use GuzzleResponse as Response;

class GuzzleRequest
{
    public function __construct($client)
    {
        $this->client = $client;
        $this->bodyFormat = 'json';
        $this->options = [
            'http_errors' => false,
        ];
    }

    public static function new(...$args)
    {
        return new self(...$args);
    }

    public function asJson()
    {
        return $this->bodyFormat('json')->contentType('application/json');
    }

    public function asFormParams()
    {
        return $this->bodyFormat('form_params')->contentType('application/x-www-form-urlencoded');
    }

    public function bodyFormat($format)
    {
        return $this->tapGuzzle($this, function ($request) use ($format) {
            $this->bodyFormat = $format;
        });
    }

    public function contentType($contentType)
    {
        return $this->withHeaders(['Content-Type' => $contentType]);
    }

    public function accept($header)
    {
        return $this->withHeaders(['Accept' => $header]);
    }

    public function withHeaders($headers)
    {
        return $this->tapGuzzle($this, function ($request) use ($headers) {
            return $this->options = array_merge_recursive($this->options, [
                'headers' => $headers
            ]);
        });
    }

    public function get($url, $queryParams = [])
    {
        return $this->send('GET', $url, [
            'query' => $queryParams,
        ]);
    }

    public function getWithHeaders($url, $queryParams = [])
    {
        return $this->send('GET', $url, [
            $this->bodyFormat => $queryParams,
        ]);
    }

    public function post($url, $params = [])
    {
        return $this->send('POST', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function patch($url, $params = [])
    {
        return $this->send('PATCH', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function put($url, $params = [])
    {
        return $this->send('PUT', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function delete($url, $params = [])
    {
        return $this->send('DELETE', $url, [
            $this->bodyFormat => $params,
        ]);
    }

    public function send($method, $url, $options)
    {
        return new Response($this->client->request($method, $url, $this->mergeOptions([
            'query' => $this->parseQueryParams($url),
        ], $options)));
    }

    protected function mergeOptions(...$options)
    {
        return array_merge_recursive($this->options, ...$options);
    }

    protected function parseQueryParams($url)
    {
        return $this->tapGuzzle([], function (&$query) use ($url) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
        });
    }

    public function tapGuzzle($value, $callback){

        $callback($value);

        return $value;
    }
}
