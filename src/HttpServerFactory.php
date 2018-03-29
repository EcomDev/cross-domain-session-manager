<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class HttpServerFactory
{
    public static function createServer(SessionStorage $sessionStorage = null)
    {
        $sessionStorage = $sessionStorage ?: new SessionStorage();

        return new HttpServer(
            new RequestHandlerFactory($sessionStorage)
        );
    }
}
