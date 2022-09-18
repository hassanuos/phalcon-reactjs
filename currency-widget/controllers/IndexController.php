<?php

use Phalcon\Http\Message\ServerRequest;
use Phalcon\Http\Message\Uri;
use GuzzleHttp\Client;

class indexController extends ControllerBase
{
    /**
     * Gets view
     */
    public function index()
    {
        echo $this->view->render('index');
    }

    // Method 1 simple curl call
    public function exchange()
    {
        $cache = new CustomCache();
        $callApi = new RequestApi();
        $cacheKey = 'saved-api-data';

        // file cache settings cacheSettings(duration(seconds), type = file/mem/apc)
        $cacheObj = $cache->cacheSettings(300);

        // get if we already have data
        $robots = $cacheObj->get($cacheKey);

        // call currency exchange api
        if (!is_array($robots)){
            $liveData = $callApi->get("{$callApi->apiBaseURL}/latest", ["base" => "USD"]);
            $liveData = json_decode($liveData, true);

            // write into cache file
            $cacheObj->save($cacheKey, $liveData);
        }else{
            // data from cache
            $liveData = $robots;
        }

        // get remaining expiry time in seconds
        $liveData['cache_expiry_time'] = $callApi->cacheExpiryTime($cacheKey);

        // in case of server issue
        if (!array_key_exists('success', $liveData)){
            $this->buildErrorResponse(400, 'common.INVALID_EXCHANGE_DATA', $liveData);
        }

        $baseCountryCode = substr(strtolower($liveData['base']), 0, 2);
        $liveData['base_country_name'] = $callApi->codeToCountry($baseCountryCode);


        // skip donation array object
        if (isset($liveData['motd'])) unset($liveData['motd'], $liveData['success']);

        // select top 10 records from the array
        $liveRates = array_slice($this->shuffle_assoc($liveData['rates']), 0, 11);

        // Convert base date format
        $liveData['date'] = date("d F Y", strtotime($liveData['date']));

        // prepare render data
        $prepareRenderData = [];
        $index = 0;
        foreach ($liveRates as $k => $val){
            $countryCode = substr(strtolower($k), 0, 2);
            $prepareRenderData[$index] = [
                'code' => $k,
                'amount' => number_format($val, 3),
                'flag_class' => "flag-icon-{$countryCode}",
                'country_name' => $callApi->codeToCountry($countryCode),
            ];
            $index++;
        }

        // replace the rates key with the latest data
        $liveData['rates'] = $prepareRenderData;

        $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $liveData);
    }

    // Method 2 Guzzle
    public function newExchange(){
        try {
            $callApi = new RequestApi();
            $cache = new CustomCache();
            $cacheKey = 'saved-api-data';

            // file cache settings cacheSettings(duration(seconds), type = file/mem/apc)
            $cacheObj = $cache->cacheSettings(300);

            // get if we already have data
            $robots = $cacheObj->get($cacheKey);

            // call currency exchange api
            if (!is_array($robots)){
                $response = ExchangeGuzzle::client()->get("{$callApi->apiBaseURL}/latest?base=USD", []);
                if($response->getStatusCode() == 200){
                    $liveData = json_decode($response->getBody(), true);
                    // write into cache file
                    $cacheObj->save($cacheKey, $liveData);
                }else{
                    // if status code is not 200
                    $this->buildErrorResponse($response->getStatusCode(), 'common.INVALID_EXCHANGE_DATA', [
                        "error" => true,
                        "message" => "Something went wrong"
                    ]);
                }

            }else{
                // data from cache
                $liveData = $robots;
            }

            // in case of server issue
            if (!array_key_exists('success', $liveData)){
                $this->buildErrorResponse(400, 'common.INVALID_EXCHANGE_DATA', $liveData);
            }

            // get remaining expiry time in seconds
            $liveData['cache_expiry_time'] = $callApi->cacheExpiryTime($cacheKey);

            $baseCountryCode = substr(strtolower($liveData['base']), 0, 2);
            $liveData['base_country_name'] = $callApi->codeToCountry($baseCountryCode);


            // skip donation array object
            if (isset($liveData['motd'])) unset($liveData['motd'], $liveData['success']);

            // select top 10 records from the array
            $liveRates = array_slice($this->shuffle_assoc($liveData['rates']), 0, 11);

            // Convert base date format
            $liveData['date'] = date("d F Y", strtotime($liveData['date']));

            // prepare render data
            $prepareRenderData = [];
            $index = 0;
            foreach ($liveRates as $k => $val){
                $countryCode = substr(strtolower($k), 0, 2);
                $prepareRenderData[$index] = [
                    'code' => $k,
                    'amount' => number_format($val, 3),
                    'flag_class' => "flag-icon-{$countryCode}",
                    'country_name' => $callApi->codeToCountry($countryCode),
                ];
                $index++;
            }

            // replace the rates key with the latest data
            $liveData['rates'] = $prepareRenderData;

            $this->buildSuccessResponse(200, 'common.SUCCESSFUL_REQUEST', $liveData);

        }catch (Exception  $e){
            $this->buildErrorResponse(500, 'common.INVALID_EXCHANGE_DATA', [
                "error" => true,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function shuffle_assoc($my_array){
        $keys = array_keys($my_array);
        shuffle($keys);
        foreach($keys as $key) {
            $new[$key] = $my_array[$key];
        }
        return $new;
    }

    public function guz(){

        // Phalcon 4.0 method compatability issue we are using 3.4.x
        $uri = new Uri('https://api.exchangerate.host/latest?base=USD');
        $request = new ServerRequest();
        $request = $request
            ->withHeader('Content-Type', ['application/json'])
            ->withMethod('GET')
            ->withUri($uri);
//            ->withParsedBody([
//                'title' => 'foo',
//                'body' => 'bar',
//                'userId' => 1001,
//            ]);

        $client = new Client;
        $response = $client->send($request);
        $body = $response->getBody();
        $contents = $body->getContents();
        print_r($contents);exit();
    }


}
