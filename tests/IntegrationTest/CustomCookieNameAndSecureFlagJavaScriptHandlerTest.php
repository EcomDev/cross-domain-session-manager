<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager\IntegrationTest;


class CustomCookieNameAndSecureFlagJavaScriptHandlerTest extends BaseIntegrationTest
{
    protected static function getServerArguments(): string
    {
        return '127.0.0.1:8080 customCookie --cookie-secure';
    }

    /** @test */
    public function customCookieIsSetInStartedServer()
    {
        $this->sendRequest('http://domain.com/');

        $this->assertArraySubset(
            [
                'HttpOnly' => true,
                'Secure' => true,
                'MaxAge' => 3600,
                'Path' => '/'
            ],
            $this->fetchCookieForDomain('customCookie', 'domain.com')
        );
    }


}
