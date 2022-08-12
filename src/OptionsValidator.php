<?php

namespace Hasanparasteh\CurlAsProxy;

final class OptionsValidator
{
    public static function isURLValid($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    public static function isHeadersValid(string $headers): bool
    {
        return true;
    }

    public static function isParamsValid(string $params): bool
    {
        return true;
    }

    public static function isBodyValid(string $body): bool
    {
        return true;
    }

    public static function isCookiesValid(string $cookies):bool
    {
        return true;
    }
}