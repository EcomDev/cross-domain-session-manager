<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class HttpServerSettings
{
    private $bind;
    private $cookieName;

    public function __construct($bind, $cookieName)
    {
        $this->bind = $bind;
        $this->cookieName = $cookieName;
    }

    public function getBind()
    {
        return $this->bind;
    }

    public function getCookieName()
    {
        return $this->cookieName;
    }
}
