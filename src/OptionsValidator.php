<?php

namespace Trend\CurlAsProxy;

final class OptionsValidator
{
    public static function isURLValid($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    public static function isMethodValid(string $method): bool
    {
        if (in_array($method, ["POST", "PUT", "PATCH", "GET", "DELETE"], true))
            return true;
        return false;
    }
}