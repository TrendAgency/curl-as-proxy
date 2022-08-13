<?php

namespace Hasanparasteh\CurlAsProxy;

class CurlMaker
{
    protected array $opt;
    protected array $body;

    public function __construct()
    {
        if (!defined('CURL_HTTP_VERSION_3')) {
            define('CURL_HTTP_VERSION_3', 30);
        }

        $this->body = API::getBody();
    }

    public function setCurlOptions(): void
    {
        $this->opt[CURLOPT_RETURNTRANSFER] = true;
        $this->opt[CURLOPT_ENCODING] = '';
        $this->opt[CURLOPT_MAXREDIRS] = 10;
        $this->opt[CURLOPT_TIMEOUT] = 0;
        $this->opt[CURLOPT_FOLLOWLOCATION] = true;
        $this->opt[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        $this->opt[CURLOPT_HTTPHEADER] = API::getHeaders();
        $this->opt[CURLOPT_SSL_VERIFYHOST] = 0;
        $this->opt[CURLOPT_SSL_VERIFYPEER] = 0;
    }

    public function setUrl(): void
    {
        $this->opt[CURLOPT_URL] = "https://jsonplaceholder.typicode.com/posts";
    }

    public function setMethod(): void
    {
        $this->opt[CURLOPT_CUSTOMREQUEST] = $this->body['method'];
    }
    public function setBody(): void
    {
        $this->opt[CURLOPT_POSTFIELDS] = json_encode($this->body['body']);
    }

    protected function doRequest(): array
    {
        $this->setCurlOptions();
        $this->setUrl();
        $this->setMethod();
        if (API::isLoadedMethod($this->body['method']))
            $this->setBody();

        // Setup Curl
        $curl = curl_init();
        curl_setopt_array($curl, $this->opt);

        // Execute Curl
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false) {
            return [
                'result' => false,
                'error' => curl_error($curl),
                'errorno' => curl_errno($curl)
            ];
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = (array) json_decode($response, true);


        return [
            'result' => true,
            'responseBody' => $response,
            'statusCode' => $status ?? 500
        ];

    }

}