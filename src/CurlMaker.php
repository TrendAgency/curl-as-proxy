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
        $this->opt[CURLOPT_SSL_VERIFYHOST] = 0;
        $this->opt[CURLOPT_SSL_VERIFYPEER] = 0;
        $this->opt[CURLOPT_HEADER] = 1;
        $this->opt[CURLOPT_POSTREDIR] = 3;
        $this->opt[CURLOPT_FRESH_CONNECT] = true;
    }


    private function setHeaders(): void
    {
        $body = API::getBody();
        if (!isset($body['headers']))
            $body['headers'][] = "Content-Type: text/html; charset=UTF-8";

        $this->opt[CURLOPT_HTTPHEADER] = $body['headers'];
    }

    private function setVersion(): void
    {
        if (isset(API::getBody()['version'])) {
            $version = API::getBody()['version'];
            $this->opt[CURLOPT_HTTP_VERSION] = match ($version) {
                1 => CURL_HTTP_VERSION_1_0,
                1.1 => CURL_HTTP_VERSION_1_1,
                2 => CURL_HTTP_VERSION_2,
                default => CURL_HTTP_VERSION_3,
            };
        } else {
            $this->opt[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_3;
        }
    }

    private function implementCookies(): void
    {
        if (!empty(API::getBody()['cookies']))
            if (is_array(API::getBody()['cookies'])) {
                $cookies = "";
                foreach (API::getBody()['cookies'] as $cookieKey => $cookieValue) {
                    $cookies .= "$cookieKey=$cookieValue;";
                }

                $this->opt[CURLOPT_HTTPHEADER][] = "Cookie: $cookies";
            } else {
                $this->opt[CURLOPT_HTTPHEADER][] = "Cookie: " . API::getBody()['cookies'];
            }
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

    private static function parseResponseHeaders(string $headers): array
    {
        $final = [];
        foreach (explode(PHP_EOL, trim($headers)) as $header) {
            $exploded = explode(":", $header);
            if (!empty($exploded[1]))
                $final[$exploded[0]] = is_numeric($exploded[1]) ? intval($exploded[1]) : trim($exploded[1]);
        }
        return $final;
    }


    private function calculatePermutations($text) {

        $permutations = array();
        $chars = str_split($text);
        for ($i = 0; $i < 2 ** strlen($text); $i++) {
            for ($j = 0; $j < strlen($text); $j++) {
                $permutations[$i][] = ($this->isBitSet($i, $j))
                    ? strtoupper($chars[$j])
                    : $chars[$j];
            }
            $permutations[$i] = implode("", $permutations[$i]);
        }
        return $permutations;
    }
    private function isBitSet($n, $offset) {
        return ($n >> $offset & 1) != 0;
    }

    private function findContentTypeKey(array $per, array $headers)
    {
        foreach ($per as $key) {
            if (isset($headers[$key]))
                return $key;
        }
        return false;
    }

    protected function doRequest(): array
    {
        $time1 =  round(microtime(true)*1000);
        $this->setCurlOptions();
        $this->setUrl();
        $this->setMethod();
        $this->setVersion();
        if (API::isLoadedMethod($this->body['method']))
            $this->setBody();

        $this->setHeaders();
        $this->implementCookies();


        // Setup Curl
        $curl = curl_init();
        curl_setopt_array($curl, $this->opt);

        // Execute Curl
        $response = curl_exec($curl);
        curl_close($curl);
        $time2 =  round(microtime(true)*1000);
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

            $key = $this->findContentTypeKey($this->calculatePermutations("content-type"), $headers);
            if ($key && str_contains($headers[$key], "application/json"))
               $response = (array)json_decode($response, true);
            else
                $response = trim(preg_replace('/\s\s+/', ' ', $response));

        return [
            'result' => true,
            'responseTime' => doubleval($time2-$time1),
            'responseBody' => $response,
            'headers' => $headers,
            'statusCode' => $status ?? 500
        ];

    }

}