<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;


use Symfony\Component\Process\Process;

class ServerDaemonFactory
{
    public function createDaemon(string $shellArguments)
    {
        return new ServerDaemon(
            new Process(sprintf('%s bin/server %s', PHP_BINARY, $shellArguments), dirname(__DIR__, 2)),
            $this->extractServerHostFromArguments($shellArguments)
        );
    }

    private function extractServerHostFromArguments(string $arguments)
    {
        $host = '127.0.0.1:8080';

        if ($arguments && !substr($arguments, 0, 1) === '-') {
            list($host) = explode(' ', $arguments, 2);
        }

        return $host;
    }

}
