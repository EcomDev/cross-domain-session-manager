<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;


use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    /**
     * @var CookieJarInterface
     */
    private $cookieJar;

    /**
     * @var ClientInterface
     */
    private $guzzleClient;

    public function __construct(CookieJarInterface $cookieJar = null, ClientInterface $guzzleClient = null)
    {
        $this->cookieJar = $cookieJar ?? new CookieJar();
        $this->guzzleClient = $guzzleClient ?? new Client([
            RequestOptions::ALLOW_REDIRECTS => [
                'max' => 3,
                'track_redirects' => true
            ]
        ]);
    }

    public function reset()
    {
        $this->cookieJar->clear();
    }

    public function requestUrlViaServer($url, ServerDaemon $server, array $headers = []): ResponseInterface
    {
        $response = $this->guzzleClient->request('GET', $url, [
            RequestOptions::HEADERS => $headers,
            RequestOptions::COOKIES => $this->cookieJar,
            RequestOptions::PROXY => sprintf('http://%s:%d', $server->getHostname(), $server->getPort())
        ]);

        return $response;
    }

    public function fetchCookiesForDomain(string $domain)
    {
        $cookies = [];
        /** @var SetCookie $item */
        foreach ($this->cookieJar as $item) {
            if ($item->matchesDomain($domain)) {
                $cookies[$item->getName()] = [
                    'Value' => $item->getValue(),
                    'Path' => $item->getPath(),
                    'Expires' => $item->getExpires(),
                    'MaxAge' => $item->getMaxAge(),
                    'Secure' => $item->getSecure(),
                    'HttpOnly' => $item->getHttpOnly(),
                    'Object' => $item
                ];
            }
        }

        return $cookies;
    }

    private function buildUrl($parts): string
    {
        return $parts['scheme'] . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . $parts['path']
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }
}
