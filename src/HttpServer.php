<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\StreamingServer;
use React\Socket\Server as SocketServer;

class HttpServer
{
    /**
     * @var RequestHandlerFactory
     */
    private $requestHandlerFactory;

    public function __construct(RequestHandlerFactory $requestHandlerFactory)
    {
        $this->requestHandlerFactory = $requestHandlerFactory;
    }

    public function run(ServerSettings $settings)
    {
        $loop = Factory::create();
        $requestHandler = $this->requestHandlerFactory->createHandler($settings, $loop);

        $server = new StreamingServer($requestHandler);

        $socket = new SocketServer($settings->getBind(), $loop);
        $server->listen($socket);
        $loop->addPeriodicTimer(1, [$requestHandler, 'cleanUp']);
        $loop->run();
    }
}
