<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\Console;


use Symfony\Component\Console\Input\InputInterface;

class OptionsMapper
{
    public function mapConsoleInput(InputInterface $input)
    {
        $values = [
            'bind' => $input->getArgument('bind'),
            'session_name' => $input->getArgument('sessionName')
        ];

        $cookieLifetime = $input->getOption('cookie-lifetime');

        $options = [
            'cookie_lifetime' => $cookieLifetime ? (int)$cookieLifetime : null,
            'cookie_domain' => $input->getOption('cookie-domain'),
            'cookie_path' => $input->getOption('cookie-path'),
            'cookie_http_only' => $input->getOption('cookie-no-http-only') ? false : null,
            'cookie_secure' => $input->getOption('cookie-secure') ?: null,
            'cookie_trim_www' => $input->getOption('cookie-trim-www') ?: null
        ];

        $filterNonNullOptions = function ($value) {
            return $value !== null;
        };

        return $values + array_filter($options, $filterNonNullOptions);
    }
}
