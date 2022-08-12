<?php

namespace Hasanparasteh\CurlAsProxy;

class Requester
{
    protected readonly array $options;
    protected readonly string $method;

    public int $statusCode;
    public array $responseBody;

    public bool $hasError = false;
    public string $error = "";

    public function __construct(array $options, string $method)
    {
        $this->options = $options;
        $this->method = $method;
    }

    public function execute(): void
    {
        $this->statusCode = 200;
        $this->responseBody = [
            'method' => $this->method,
            'options'=> $this->options
        ];
    }
}