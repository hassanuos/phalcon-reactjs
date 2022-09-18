<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

/**
 * ACL checks
 */
$app->before(new AccessMiddleware());

/**
 * Insert your Routes below
 */

/**
 * Index
 */
$index = new MicroCollection();
$index->setHandler('IndexController', true);
// Gets index
$index->get('/', 'index');
// exchange data method 1 simple curl call
$index->get('/get-exchange-data', 'exchange');
// exchange data call 2 Guzzle wrapper
$index->get('/get-exchange-data-guzzle', 'newExchange');
// Adds index routes to $app
$app->mount($index);

/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, 'Not Found')->sendHeaders();
    $app->response->setContentType('application/json', 'UTF-8');
    $app->response->setJsonContent(array(
        'status' => 'error',
        'code' => '404',
        'messages' => 'URL Not found',
    ));
    $app->response->send();
});

/**
 * Error handler
 */
$app->error(
    function ($exception) {
        print_r('An error has occurred');
    }
);
