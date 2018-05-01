<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;

use Psr\Http\Message\ResponseInterface;

class RedirectBasedHandlerTest extends BaseIntegrationTest
{
    protected static function getServerArguments(): string
    {
        return '';
    }

    /** @test */
    public function cookieIsSetForAllDomainsViaFollowingRedirects()
    {
        $response = $this->sendRequest(
            sprintf(
                'http://base-domain.com/sso?redirect=%s',
                urlencode('http://another-domain.com/some/page/')
            )
        );

        $this->assertContains(
            'http://another-domain.com/some/page/',
            $response->getHeader('X-Guzzle-Redirect-History')
        );

        $baseDomainCookie = $this->fetchCookieForDomain('sharedSession', 'base-domain.com');
        $sharedDomainCookie = $this->fetchCookieForDomain('sharedSession', 'another-domain.com');

        $this->assertNotEmpty($baseDomainCookie);
        $this->assertNotEmpty($sharedDomainCookie);

        $this->assertSame($baseDomainCookie['Value'], $sharedDomainCookie['Value']);
    }

    /** @test */
    public function validationCookieIsSetWhenValidationCookieNameIsProvided()
    {
        $this->sendRequest(sprintf(
            'http://base-domain.com/sso?redirect=%s&validation_cookie=token_is_set',
            urlencode('http://another-domain.com/some/page/')
        ));

        $this->assertArraySubset(
            [
                'Value' => 1,
                'MaxAge' => 3600,
                'Path' => '/'
            ],
            $this->fetchCookieForDomain('token_is_set', 'another-domain.com')
        );
    }


}
