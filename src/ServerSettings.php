<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class ServerSettings
{
    private $bind;
    private $sessionName;
    private $cookieOptions;

    public function __construct(string $bind, string $sessionName, array $cookieOptions)
    {
        $this->bind = $bind;
        $this->sessionName = $sessionName;
        $this->cookieOptions = $cookieOptions;
    }

    public function getBind()
    {
        return $this->bind;
    }

    public function getSessionName()
    {
        return $this->sessionName;
    }

    public function getSessionCheckName()
    {
        return sprintf('%s-check', $this->sessionName);
    }

    public function getCookieOptions()
    {
        return $this->cookieOptions;
    }
}
