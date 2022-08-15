<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Trend\CurlAsProxy\API;
use Trend\CurlAsProxy\Init;
use Trend\CurlAsProxy\Requester;

require dirname(__DIR__) . '/vendor/autoload.php';


API::setupBasicHeaders();

$initiator = new Init();

if ($initiator->init()) {


    $requester = new Requester();

    $requester->execute();

    if ($requester->hasError) {
        API::response(500, $requester->error);
    } else {
        API::response($requester->statusCode, $requester->responseBody);
    }

} else {
    API::response(400, [
        'result' => false,
        'message' => "INIT_FAILED"
    ]);
}
