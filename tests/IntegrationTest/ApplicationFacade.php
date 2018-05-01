<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;

use Psr\Http\Message\ResponseInterface;

class ApplicationFacade
{

    /**
     * @var ServerDaemonFactory
     */
    private $daemonFactory;

    /** @var ServerDaemon */
    private $server;

    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(ServerDaemonFactory $daemonFactory = null, HttpClient $httpClient = null)
    {
        $this->daemonFactory = $daemonFactory ?? new ServerDaemonFactory();
        $this->httpClient = $httpClient ?? new HttpClient();
    }

    public function startServer(string $arguments = ''): ServerDaemon
    {
        $this->server = $this->daemonFactory->createDaemon($arguments);
        $this->server->start();
        return $this->server;
    }

    public function stopServer()
    {
        unset($this->server);
    }

    public function reset()
    {
        $this->httpClient->reset();
    }

    public function requestUrl(string $url, array $headers = []): ResponseInterface
    {
        return $this->httpClient->requestUrlViaServer($url, $this->server, $headers);
    }

    public function fetchCookieForDomain(string $cookieName, string $domain): array
    {
        return $this->httpClient->fetchCookiesForDomain($domain)[$cookieName] ?? [];
    }
}
