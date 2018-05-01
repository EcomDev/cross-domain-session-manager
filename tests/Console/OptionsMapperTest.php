<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\Console;


use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\Input;

class OptionsMapperTest extends TestCase
{
    /** @var OptionsMapper */
    private $consoleOptionsMapper;

    protected function setUp()
    {
        $this->consoleOptionsMapper = new OptionsMapper();
    }

    /** @test */
    public function mapsArrayFromEmptyConsoleInput()
    {
        $input = $this->createConsoleInput([]);
        $this->assertEquals(
            [
                'bind' => '0.0.0.0:8080',
                'session_name' => 'sharedSession'
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }

    /** @test */
    public function mapsArrayFromSpecifiedArguments()
    {
        $input = $this->createConsoleInput([
            '127.0.0.1:8888',
            'someSessionName'
        ]);

        $this->assertEquals(
            [
                'bind' => '127.0.0.1:8888',
                'session_name' => 'someSessionName'
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }

    /** @test */
    public function mapsCookieLifetimeOption()
    {
        $input = $this->createConsoleInput([
            '--cookie-lifetime',
            '60'
        ]);

        $this->assertEquals(
            [
                'bind' => '0.0.0.0:8080',
                'session_name' => 'sharedSession',
                'cookie_lifetime' => 60
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }

    /** @test */
    public function mapsCookieDomainOption()
    {
        $input = $this->createConsoleInput([
            '--cookie-domain',
            'example.com'
        ]);

        $this->assertEquals(
            [
                'bind' => '0.0.0.0:8080',
                'session_name' => 'sharedSession',
                'cookie_domain' => 'example.com'
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }

    /** @test */
    public function mapsCookiePathOption()
    {
        $input = $this->createConsoleInput([
            '--cookie-path',
            '/path/'
        ]);

        $this->assertEquals(
            [
                'bind' => '0.0.0.0:8080',
                'session_name' => 'sharedSession',
                'cookie_path' => '/path/'
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }

    /** @test */
    public function mapsTrimWwwInCookieOption()
    {
        $input = $this->createConsoleInput([
            '--cookie-trim-www'
        ]);

        $this->assertEquals(
            [
                'bind' => '0.0.0.0:8080',
                'session_name' => 'sharedSession',
                'cookie_trim_www' => true
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }


    /** @test */
    public function mapsCookieHttpOnlyOption()
    {
        $input = $this->createConsoleInput([
            '--cookie-no-http-only'
        ]);

        $this->assertEquals(
            [
                'bind' => '0.0.0.0:8080',
                'session_name' => 'sharedSession',
                'cookie_http_only' => false
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }

    /** @test */
    public function mapsCookieSecureOption()
    {
        $input = $this->createConsoleInput([
            '--cookie-secure'
        ]);

        $this->assertEquals(
            [
                'bind' => '0.0.0.0:8080',
                'session_name' => 'sharedSession',
                'cookie_secure' => true
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }

    /** @test */
    public function mapsAllOptionsTogetherWithArguments()
    {
        $input = $this->createConsoleInput([
            '--cookie-secure',
            '--cookie-no-http-only',
            '--cookie-lifetime',
            '99',
            '--cookie-domain',
            'example.org',
            '--cookie-path',
            '/'
        ]);

        $this->assertEquals(
            [
                'bind' => '0.0.0.0:8080',
                'session_name' => 'sharedSession',
                'cookie_lifetime' => 99,
                'cookie_domain' => 'example.org',
                'cookie_path' => '/',
                'cookie_http_only' => false,
                'cookie_secure' => true
            ],
            $this->consoleOptionsMapper->mapConsoleInput($input)
        );
    }

    private function createConsoleInput(array $parameters): Input
    {
        array_unshift($parameters, '');

        return new ArgvInput(
            $parameters,
            ServerOptionDefinitionFactory::createDefinition()
        );
    }
}
