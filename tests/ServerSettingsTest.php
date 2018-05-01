<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use PHPUnit\Framework\TestCase;

class ServerSettingsTest extends TestCase
{
    /**
     * @test
     * @testWith ["sessionName", "sessionName-check"]
     *           ["identifier", "identifier-check"]
     */
    public function usesSessionNameForSessionValidationCookie($sessionName, $expectedCheckName)
    {
        $sessionModel = new ServerSettings('0.0.0.0:8888', $sessionName, ['lifetime' => 3600]);
        $this->assertEquals($expectedCheckName, $sessionModel->getSessionCheckName());
    }
}
