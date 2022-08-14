<?php

namespace Trend\CurlAsProxy;

final class Init
{
    public static function requiredOptions(): array
    {
        return [
            'url',
            'method'
        ];
    }

    public static function getOptionsList(): array
    {
        $body = API::getBody();
        $options = [];

        foreach (self::requiredOptions() as $option) {
            $options[$option] = $body[$option];
        }

        if (isset($body['body']))
            $options['body'] = $body['body'];

        return $options;
    }

    private function isRequiredOptionsSet(): bool
    {
        $body = API::getBody();
        foreach (self::requiredOptions() as $opt) {
            if (!isset($body[$opt])) {
                return false;
            }
        }

        return true;
    }

    public function isRequiredOptionsValid(): bool
    {
        if (!$this->isRequiredOptionsSet())
            return false;

        $options = self::getOptionsList();

        if (!OptionsValidator::isURLValid($options['url']))
            return false;

        if (!OptionsValidator::isMethodValid($options['method']))
            return false;

        if (API::isLoadedMethod($options['method']) && !isset($options['body']))
            return false;

        return true;

    }

    public function init(): bool|array
    {
        if ($this->isRequiredOptionsValid() && API::getRequestMethod() == "POST") {
            return true;
        }

        return false;
    }
}