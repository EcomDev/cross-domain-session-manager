<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class RequestHandlerFactory
{
    /**
     * @var SessionStorage
     */
    private $sessionStorage;

    public function __construct(SessionStorage $sessionStorage)
    {
        $this->sessionStorage = $sessionStorage;
    }

    public function createHandler(HttpServerSettings $settings)
    {
        return new RequestHandler($settings, $this->sessionStorage);
    }

}
