<?php

namespace Trend\CurlAsProxy;

class API
{
    public static function response(int|null $status, array|string|null $response): void
    {
        if (is_null($status) || is_null($response)) {
            die();
        }

        http_response_code($status);
        echo json_encode($response, 128);
    }

    public static function setupBasicHeaders(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    }

    public static function getRequestMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getCookies(): array
    {
        return $_COOKIE;
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