<?php

namespace Trend\CurlAsProxy;

class API
{
    public static function response(int|null $status, array|string|null $response, string $contentType = "application/json; charset=UTF-8"): void
    {
        if (is_null($status) || is_null($response)) {
            http_response_code(500);
            die();
        }

        header("Content-Type: $contentType");
        http_response_code($status);

        if (str_contains(strtolower($contentType), "application/json"))
            echo json_encode($response, 128);
        else
            echo $response;
    }

    public static function setupBasicHeaders(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }

    public static function getRequestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getBody(): array
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    public static function isLoadedMethod(string $method): bool
    {
        return !in_array($method, ["GET", "DELETE"], true);
    }
}