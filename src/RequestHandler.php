<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

class RequestHandler
{
    const TOKEN_JAVASCRIPT = '
(function (doc) {
    var token = doc.createElement("script");
    token.type = "text/javascript";
    token.src = %s;
    token.async = true;
    doc.body.appendChild(token);
})(document);
        ';
    /**
     * @var HttpServerSettings
     */
    private $settings;

    /**
     * @var SessionStorage
     */
    private $sessionStorage;

    public function __construct(HttpServerSettings $settings, SessionStorage $sessionStorage)
    {
        $this->settings = $settings;
        $this->sessionStorage = $sessionStorage;
    }

    private function buildSessionCookie(string $sessionCookie, string $domain)
    {
        return sprintf('%s=%s; Path=/; Domain=%s; HttpOnly', $this->settings->getCookieName(), $sessionCookie, $domain);
    }

    private function assignSessionId(ServerRequestInterface $request)
    {
        $query = $request->getQueryParams();
        if (isset($query['token'])) {
            return $this->sessionStorage->resolveToken($query['token']);
        }

        $cookies = $request->getCookieParams();
        if (isset($cookies[$this->settings->getCookieName()])) {
            return $this->sessionStorage->validateSession($cookies[$this->settings->getCookieName()]);
        }

        return $this->sessionStorage->createSession();
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $sessionId = $this->assignSessionId($request);

        return new Response(
            200,
            [
                'Content-Type' => 'text/javascript',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Set-Cookie' => $this->buildSessionCookie(
                    $sessionId,
                    $request->getUri()->getHost()
                )
            ],
            $this->buildResponseBody($request, $sessionId)
        );
    }

    private function buildResponseBody(ServerRequestInterface $request, string $sessionId)
    {
        $query = $request->getQueryParams();

        if (isset($query['token'])) {
            return '// token is already assigned';
        }

        $tokenUrl = sprintf(
            '%s?token=%s',
            $request->getUri()->getPath(),
            $this->sessionStorage->createToken($sessionId)
        );

        return sprintf(self::TOKEN_JAVASCRIPT, json_encode($tokenUrl, JSON_UNESCAPED_SLASHES));
    }
}
