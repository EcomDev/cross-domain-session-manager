<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\Console;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ServerOptionDefinitionFactory
{
    public static function createDefinition()
    {
        return new InputDefinition([
            new InputArgument(
                'bind',
                InputArgument::OPTIONAL,
                'TCP/IP Bind for server to listen to',
                '0.0.0.0:8080'
            ),
            new InputArgument(
                'sessionName',
                InputArgument::OPTIONAL,
                'Session Cookie name',
                'sharedSession'
            ),
            new InputOption(
                'cookie-lifetime',
                null,
                InputOption::VALUE_REQUIRED,
                'Default lifetime for the cookies produced by server. Defaults to 1 hour.'
            ),
            new InputOption(
                'cookie-domain',
                null,
                InputOption::VALUE_REQUIRED,
                'Default domain for the cookies produced by server. Defaults to 1 hour.'
            ),
            new InputOption(
                'cookie-no-http-only',
                null,
                InputOption::VALUE_NONE,
                'Disabled HttpOnly option for the cookies produced by server'
            ),
            new InputOption(
                'cookie-secure',
                null,
                InputOption::VALUE_NONE,
                'Enabled Secure option for the cookies produced by server'
            ),
            new InputOption(
                'cookie-path',
                null,
                InputOption::VALUE_REQUIRED,
                'Default Path option for the cookies produced by server'
            ),
            new InputOption(
                'cookie-trim-www',
                null,
                InputOption::VALUE_NONE,
                'Trims www. from domain in set cookies'
            )
        ]);
    }
}
