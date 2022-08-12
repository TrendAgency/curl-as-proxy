<?php

use Hasanparasteh\CurlAsProxy\API;
use Hasanparasteh\CurlAsProxy\Init;
use Hasanparasteh\CurlAsProxy\Requester;

require dirname(__DIR__) . '/vendor/autoload.php';


API::setupBasicHeaders();

$initiator = new Init();

if ($initiator->init()) {
    $requester = new Requester($initiator->getOptionsList(), API::getRequestMethod());

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
