<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;

class CookieOptionsJavaScriptHandlerTest extends BaseIntegrationTest
{
    static protected function getServerArguments(): string
    {
        return '--cookie-lifetime=5 --cookie-no-http-only '
             . '--cookie-path=/path --cookie-domain=example.org --cookie-trim-www';
    }

    /** @test */
    public function reducesCookieLifetimeToConsoleOption()
    {
        $this->sendRequest('http://example.org/path');

        $this->assertArraySubset(
            [
                'MaxAge' => 5
            ],
            $this->fetchCookieForDomain('sharedSession', 'example.org')
        );
    }


    /** @test */
    public function cookieIsExpiredAfterLifetimeOnServerHasPassed()
    {
        $this->sendRequest('http://example.org/path');
        $cookieOld = $this->fetchCookieForDomain('sharedSession', 'example.org');

        sleep(6);
        $cookieOld['Object']->setExpires(time() + 6); // Make cookie non expired

        $this->sendRequest('http://example.org/path');
        $cookieNew = $this->fetchCookieForDomain('sharedSession', 'example.org');

        $this->assertNotEquals($cookieNew['Value'], $cookieOld['Value']);
    }

    /** @test */
    public function restrictsPathToOptionValue()
    {
        $this->sendRequest('http://example.org/');

        $this->assertArraySubset(
            [
                'Path' => '/path'
            ],
            $this->fetchCookieForDomain('sharedSession', 'example.org')
        );
    }

    /** @test */
    public function makesCookieAccessibleByJavaScript()
    {
        $this->sendRequest('http://example.org/');

        $this->assertArraySubset(
            [
                'HttpOnly' => false
            ],
            $this->fetchCookieForDomain('sharedSession', 'example.org')
        );
    }

    /** @test */
    public function usesConsoleOptionOfDomainForSubDomains()
    {
        $this->sendRequest('http://item.example.org/');

        $this->assertNotEmpty($this->fetchCookieForDomain('sharedSession', 'blog.example.org'));
    }

    /** @test */
    public function fallsBackToCurrentDomainIfParentDomainIsNotPartOfParent()
    {
        $this->sendRequest('http://item-example.org/');

        $this->assertNotEmpty($this->fetchCookieForDomain('sharedSession', 'item-example.org'));
    }

    /** @test */
    public function trimsWwwInDomainName()
    {
        $this->sendRequest('http://www.domain.com/');

        $this->assertNotEmpty($this->fetchCookieForDomain('sharedSession', 'domain.com'));
    }

}
