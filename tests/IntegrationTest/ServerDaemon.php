<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;


use Symfony\Component\Process\Process;

class ServerDaemon
{
    const DEFAULT_START_TIMEOUT = 0.5;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var string
     */
    private $serverHost;

    public function __construct(Process $process, $serverHost)
    {
        $this->process = $process;
        $this->serverHost = $serverHost;
    }

    public function getHostname()
    {
        list($serverHostname, ) = explode(':', $this->serverHost, 2);
        return $serverHostname;
    }

    public function getPort()
    {
        list(, $serverPort) = explode(':', $this->serverHost, 2);
        return $serverPort;
    }

    public function start(float $timeout = self::DEFAULT_START_TIMEOUT)
    {
        $this->process->start();
        $this->waitForServerToStart($timeout);
    }

    private function waitForServerToStart(float $timeout)
    {
        $startTime = microtime(true);

        do {
            $passedTime = microtime(true) - $startTime;
            if ($passedTime > $timeout) {
                throw new \RuntimeException(sprintf(
                    'Server start has timed out, here is the command line output: %s %s',
                    $this->process->getOutput(),
                    $this->process->getErrorOutput()
                ));
            }

            $socket = @fsockopen($this->getHostname(), $this->getPort());
        } while (!is_resource($socket));

        fclose($socket);
    }

    public function __destruct()
    {
        $this->process->stop();
    }
}
