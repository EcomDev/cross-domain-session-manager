<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;

use PHPUnit\Framework\TestCase;

class HttpServerSettingsFactoryTest extends TestCase
{
    /** @var ServerSettingsFactory */
    private $factory;

    protected function setUp()
    {
        $this->factory = new ServerSettingsFactory();
    }

    /** @test */
    public function createsHttpServerSettingsFromArguments()
    {
        $this->assertEquals(
            new ServerSettings('127.0.0.1:8080', 'session', [
                'lifetime' => 60,
                'path' => '/path/',
                'http-only' => false,
                'secure' => true,
                'domain' => 'test.com',
                'trim-www' => true
            ]),
            $this->factory->createFromArray([
                'bind' => '127.0.0.1:8080',
                'session_name' => 'session',
                'cookie_lifetime' => 60,
                'cookie_path' => '/path/',
                'cookie_http_only' => false,
                'cookie_secure' => true,
                'cookie_domain' => 'test.com',
                'cookie_trim_www' => true
            ])
        );
    }

    /** @test */
    public function createsDefaultHttpServerSettingsIfEmptyArrayPassed()
    {
        $this->assertEquals(
            new ServerSettings(
                '0.0.0.0:8080',
                'sharedSession',
                [
                    'lifetime' => 3600,
                    'path' => '/',
                    'http-only' => true,
                    'secure' => false
                ]
            ),
            $this->factory->createFromArray([])
        );
    }
}
