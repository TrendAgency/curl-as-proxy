<?php

namespace Trend\CurlAsProxy;

class Requester extends CurlMaker
{
    public int|null $statusCode;
    public array|string|null $responseBody;

    public bool $hasError = false;
    public array $error = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function execute(): void
    {
        $result = $this->doRequest();

        if (!$result['result']) {
            $this->hasError = true;
            $this->error = ['result' => false, 'error' => $result['error'] ?? "UNKNOWN"];
        } else {
            $this->statusCode = $result['statusCode'];
            $this->responseBody = [
                'result' => true,
                'body' => $result['responseBody'],
                'headers' => $result['headers'],
                'statusCode' => $result['statusCode'],
                'responseTime' => $result['responseTime']
            ];
        }
    }
}