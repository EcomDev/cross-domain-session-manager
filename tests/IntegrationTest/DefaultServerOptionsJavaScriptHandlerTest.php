<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;

use Psr\Http\Message\ResponseInterface;

class DefaultServerOptionsJavaScriptHandlerTest extends BaseIntegrationTest
{
    protected static function getServerArguments(): string
    {
        return '';
    }

    /** @test */
    public function cookieIsSetOnInitialVisitToPage()
    {
        $this->sendRequest('http://domain.com/');

        $this->assertArraySubset(
            [
                'HttpOnly' => true,
                'Secure' => false,
                'MaxAge' => 3600,
                'Path' => '/'
            ],
            $this->fetchCookieForDomain('sharedSession', 'domain.com')
        );
    }

    /** @test */
    public function returnsTokenInclusionJavaScriptWhenNoTokenIsProvided()
    {
        $response = $this->sendRequest('http://base-domain.com/script');

        $this->assertStringMatchesFormat(
            '(function (doc) {
    var token = doc.createElement("script");
    token.type = "text/javascript";
    token.src = "/script?token=%x";
    token.async = true;
    doc.body.appendChild(token);
})(document);',
            $response->getBody()->getContents()
        );
    }

    /** @test */
    public function serverResponseFromServerIsNotCached()
    {
        $response = $this->sendRequest('http://base-domain.com/');

        $this->assertArraySubset(
            [
                'Cache-Control' => ['no-cache, no-store, must-revalidate'],
                'Pragma' => ['no-cache'],
                'Expires' => ['Thu, 01 Jan 1970 00:00:00 +0000']
            ],
            $response->getHeaders()
        );
    }

    /** @test */
    public function cookieIsSetToTheValueAsOnAnotherDomainThatIncludesTokenScript()
    {
        $baseDomainResponse = $this->sendRequest('http://base-domain.com/script');

        $tokenUrl = $this->buildTokenUrl(
            $baseDomainResponse,
            'another-domain.com'
        );

        $this->sendRequest($tokenUrl);

        $cookie = $this->fetchCookieForDomain('sharedSession', 'base-domain.com');

        $this->assertArraySubset(
            [
                'HttpOnly' => true,
                'Secure' => false,
                'MaxAge' => 3600,
                'Path' => '/',
                'Value' => $cookie['Value']
            ],
            $this->fetchCookieForDomain('sharedSession', 'another-domain.com')
        );
    }

    /** @test */
    public function tokenResponseDispatchesEventToNotifyAboutReadyToUseSession()
    {
        $baseDomainResponse = $this->sendRequest('http://base-domain.com/script');

        $tokenUrl = $this->buildTokenUrl(
            $baseDomainResponse,
            'another-domain.com'
        );

        $this->sendRequest($tokenUrl);

        $cookie = $this->fetchCookieForDomain('sharedSession', 'base-domain.com');

        $this->assertArraySubset(
            [
                'HttpOnly' => true,
                'Secure' => false,
                'MaxAge' => 3600,
                'Path' => '/',
                'Value' => $cookie['Value']
            ],
            $this->fetchCookieForDomain('sharedSession', 'another-domain.com')
        );

    }

    /** @test */
    public function keepsWwwByDefaultInDomainName()
    {
        $this->sendRequest('http://www.domain.com/');

        $this->assertEmpty($this->fetchCookieForDomain('sharedSession', 'domain.com'));
        $this->assertNotEmpty($this->fetchCookieForDomain('sharedSession', 'www.domain.com'));
    }


    /** @test */
    public function thereAreNoRedirectsDoneByServer()
    {
        $response = $this->sendRequest('http://domain.com/');

        $this->assertEmpty($response->getHeaderLine('X-Guzzle-Redirect-History'));
    }

    private function buildTokenUrl(ResponseInterface $response, $domainName)
    {
        $script = $response->getBody()->getContents();
        preg_match('~token.src = "(.*?)";~', $script, $match);

        return sprintf('http://%s%s', $domainName, $match[1]);
    }
}
