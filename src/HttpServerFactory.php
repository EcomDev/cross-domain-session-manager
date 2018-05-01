<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class HttpServerFactory
{
    /**
     * @var SessionStorageFactory
     */
    private $storageFactory;

    public function __construct(SessionStorageFactory $storageFactory = null)
    {
        $this->storageFactory = $storageFactory ?? new SessionStorageFactory();
    }

    public function createServer()
    {
        return $this->createServerWithStorage($this->storageFactory->createEmpty());
    }

    public function createServerFromPersistedState(string $stateFile)
    {
        return $this->createServerWithStorage(
            $this->storageFactory->createFromFile($stateFile)
        );
    }

    private function createServerWithStorage(SessionStorage $sessionStorage)
    {
        return new HttpServer(
            new RequestHandlerFactory($sessionStorage)
        );
    }
}
