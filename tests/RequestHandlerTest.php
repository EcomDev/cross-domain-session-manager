<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use PHPUnit\Framework\TestCase;
use React\Http\Response;

class RequestHandlerTest extends TestCase
{
    /** @test */
    public function createsNonCachedJavaScriptResponseOnMainDomain()
    {
        $this->markTestSkipped('not implemented yet');
    }
}
