<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use React\EventLoop\LoopInterface;

class RequestHandlerFactory
{
    /**
     * @var SessionStorage
     */
    private $sessionStorage;
    /**
     * @var CookieHeaderGeneratorFactory
     */
    private $cookieHeaderGeneratorFactory;

    public function __construct(
        SessionStorage $sessionStorage,
        CookieHeaderGeneratorFactory $cookieHeaderGeneratorFactory = null
    ) {
        $this->sessionStorage = $sessionStorage;
        $this->cookieHeaderGeneratorFactory = $cookieHeaderGeneratorFactory ?? new CookieHeaderGeneratorFactory(
            new RealTimeManager()
        );
    }

    public function createHandler(ServerSettings $settings)
    {
        return new RequestHandler(
            $settings,
            $this->sessionStorage,
            $this->cookieHeaderGeneratorFactory->createGeneratorWithOptions(
                $settings->getCookieOptions()
            )
        );
    }

}
