<?php

namespace Trend\CurlAsProxy;

use Exception;

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

    public static function isHeaderValid($headers): bool
    {
        if (!is_array($headers))
            return false;

        $arr = [];
        foreach ($headers as $header) {
            $checkContentType = explode(":", $header);
            $arr[$checkContentType[0]] = $checkContentType[1];
        }

        if (!array_key_exists("Content-Type", $arr))
            return false;

        return true;
    }

    public static  function isJsonValid(string $json):bool
    {
        try {
            json_decode($json, null, flags: JSON_THROW_ON_ERROR);
            return true;
        } catch  (Exception $e) {
            return false;
        }
    }

}