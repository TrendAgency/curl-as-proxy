<?php

namespace Trend\CurlAsProxy;

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
        $this->opt[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_3;
        $this->opt[CURLOPT_HTTPHEADER] = API::getBody()['headers'];
        $this->opt[CURLOPT_SSL_VERIFYHOST] = 0;
        $this->opt[CURLOPT_SSL_VERIFYPEER] = 0;
        $this->opt[CURLOPT_HEADER] = 1;
        $this->opt[CURLOPT_POSTREDIR] = 3;


    }

    public function setUrl(): void
    {
        $this->opt[CURLOPT_URL] = $this->body['url'];
    }

    public function setMethod(): void
    {
        $this->opt[CURLOPT_CUSTOMREQUEST] = $this->body['method'];
    }

    public function setBody(): void
    {
        $this->opt[CURLOPT_POSTFIELDS] = json_encode($this->body['body']);
    }

    private function parseResponseHeaders(string $headers): array
    {
        $final = [];
        foreach (explode(PHP_EOL, trim($headers)) as $header) {
            $exploded = explode(":", $header);
            $final[$exploded[0]] = is_numeric($exploded[1]) ? intval($exploded[1]) : trim($exploded[1]);
        }
        return $final;
    }

    private function handleBadHttpStatusCodes(int $statusCode, array|string|null $message = null)
    {
        switch ($statusCode) {
            case 400:
                return ['result' => false, "error" => $message ?? "BadRequest"];
            case 500:
                return ['result' => false, "error" => "Server Side Error"];
            case 405:
                return ['result' => false, "error" => "Method Not Allowed"];
        }
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

        // Parse Header
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = $this->parseResponseHeaders(substr($response, 0, $header_size));

        // Get status and response
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response = substr($response, $header_size);

        if (in_array($status, [405, 400, 500]))
            return $this->handleBadHttpStatusCodes($status, $response);

        if(str_contains(strtolower($headers['Content-Type']), "application/json"))
            $response = (array)json_decode($response, true);
        else
            $response = trim(preg_replace('/\s\s+/', ' ', $response));

        return [
            'result' => true,
            'responseBody' => $response,
            'headers' => $headers,
            'statusCode' => $status ?? 500
        ];

    }

}