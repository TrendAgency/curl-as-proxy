<?php

namespace Hasanparasteh\CurlAsProxy;

final class Init
{
    public function requiredOptions(): array
    {
        $requiredOptionsList = [
            'url',
            'headers',
            'params',
            'cookies'
        ];

        if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'DELETE'], true)) {
            return $requiredOptionsList;
        }

        $requiredOptionsList[] = 'body';
        return $requiredOptionsList;
    }

    public function getOptionsList(): array
    {
        $options = [];
        foreach ($this->requiredOptions() as $option) {
            $options[$option] = $_REQUEST[$option];
        }
        return $options;
    }

    private function isRequiredOptionsSet(): bool
    {
        foreach (self::requiredOptions() as $opt) {
            if (!isset($_REQUEST[$opt])) {
                return false;
            }
        }

        return true;
    }

    public function isRequiredOptionsValid(): bool
    {
        if (!self::isRequiredOptionsSet())
            return false;

        $options = $this->getOptionsList();

        if (!OptionsValidator::isURLValid($options['url']))
            return false;

        if (!OptionsValidator::isParamsValid($options['params']))
            return false;

        if (!OptionsValidator::isHeadersValid($options['headers']))
            return false;

        if(!OptionsValidator::isCookiesValid($options['cookies']))
            return false;

        if (isset($options['body']) && !OptionsValidator::isBodyValid($options['body']))
            return false;

        return true;
    }

    public function init(): bool|array
    {
        if ($this->isRequiredOptionsValid()) {
            return true;
        }

        return false;
    }
}