<?php

namespace Hasanparasteh\CurlAsProxy;

final class OptionsValidator
{
    public static function isURLValid($url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    public static function isHeadersValid($headers): bool
    {
        return true;
    }

    public static function isParamsValid($params): bool
    {
        return true;
    }

    public static function isBodyValid($body): bool
    {
        return true;
    }
}