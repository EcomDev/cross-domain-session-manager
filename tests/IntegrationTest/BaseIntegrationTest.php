<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class BaseIntegrationTest extends TestCase
{
    /** @var ApplicationFacade */
    private static $testApplication;

    abstract static protected function getServerArguments(): string;

    public static function setUpBeforeClass()
    {
        self::$testApplication = new ApplicationFacade();
        self::$testApplication->startServer(static::getServerArguments());
    }

    public static function tearDownAfterClass()
    {
        self::$testApplication = null;
    }

    protected function tearDown()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->getApplication()->reset();
    }


    public function getApplication(): ApplicationFacade
    {
        return self::$testApplication;
    }

    protected function startServer(string $arguments = ''): ServerDaemon
    {
        return $this->getApplication()->startServer($arguments);
    }

    protected function sendRequest($url, array $headers = []): ResponseInterface
    {
        $response = $this->getApplication()->requestUrl($url, $headers);
        return $response;
    }

    protected function fetchCookieForDomain(string $cookieName, string $domain): array
    {
        return $this->getApplication()->fetchCookieForDomain($cookieName, $domain);
    }
}
