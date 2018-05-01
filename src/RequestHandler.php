<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Response;

class RequestHandler
{
    const TOKEN_JAVASCRIPT = '(function (doc) {
    var token = doc.createElement("script");
    token.type = "text/javascript";
    token.src = %s;
    token.async = true;
    doc.body.appendChild(token);
})(document);';
    const NO_CACHE_HEADERS = [
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'Pragma' => 'no-cache',
        'Expires' => 'Thu, 01 Jan 1970 00:00:00 +0000',
    ];
    /**
     * @var ServerSettings
     */
    private $settings;

    /**
     * @var SessionStorage
     */
    private $sessionStorage;

    /**
     * @var CookieHeaderGenerator
     */
    private $cookieHeaderGenerator;

    public function __construct(
        ServerSettings $settings,
        SessionStorage $sessionStorage,
        CookieHeaderGenerator $cookieHeaderGenerator
    ) {
        $this->settings = $settings;
        $this->sessionStorage = $sessionStorage;
        $this->cookieHeaderGenerator = $cookieHeaderGenerator;
    }

    private function buildSessionCookie(string $sessionCookie, string $domain)
    {
        return $this->cookieHeaderGenerator->generateCookie(
            $this->settings->getSessionName(),
            $sessionCookie,
            ['domain' => $domain]
        );
    }

    private function assignSessionId(ServerRequestInterface $request)
    {
        $query = $request->getQueryParams();
        if (isset($query['token'])) {
            return $this->sessionStorage->resolveToken($query['token']);
        }

        $cookies = $request->getCookieParams();
        if (isset($cookies[$this->settings->getSessionName()])) {
            return $this->sessionStorage->validateSession($cookies[$this->settings->getSessionName()]);
        }

        return $this->sessionStorage->createSession();
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $sessionId = $this->assignSessionId($request);

        if ($this->isRedirectRequest($request)) {
            return $this->createTokenRedirectResponse($request, $sessionId);
        }

        return $this->createJavaScriptResponse($request, $sessionId);
    }

    public function cleanUp()
    {
        $lifeTime = $this->settings->getCookieOptions()['lifetime'] ?? 0;
        if ($lifeTime) {
            $this->sessionStorage->cleanExpired($lifeTime);
        }
    }

    /**
     * @param $statusCode
     * @param $headers
     * @param $body
     *
     * @return Response
     */
    private function createResponse($statusCode, $headers, $body): Response
    {
        return new Response(
            $statusCode,
            $headers,
            $body
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @param $sessionId
     *
     * @return Response
     */
    private function createJavaScriptResponse(ServerRequestInterface $request, $sessionId): Response
    {
        $query = $request->getQueryParams();

        $responseBody = "document.dispatchEvent(new Event('session_complete',{bubbles:true}));";

        if (!isset($query['token'])) {
            $tokenUrl = sprintf(
                '%s?token=%s',
                $request->getUri()->getPath(),
                $this->sessionStorage->createToken($sessionId)
            );

            $responseBody = sprintf(self::TOKEN_JAVASCRIPT, json_encode($tokenUrl, JSON_UNESCAPED_SLASHES));
        }

        return $this->createResponse(
            200,
            self::NO_CACHE_HEADERS + [
                'Content-Type' => 'text/javascript',
                'Set-Cookie' => $this->buildSessionCookie($sessionId, $request->getUri()->getHost())
            ],
            $responseBody
        );
    }

    public function createTokenRedirectResponse(ServerRequestInterface $request, string $sessionId)
    {
        $redirectUrl = $request->getQueryParams()['redirect'];
        $isTokenAlready = isset($request->getQueryParams()['token']);
        $validationCookie = $request->getQueryParams()['validation_cookie'] ?? null;

        $redirectUrlParts = parse_url($redirectUrl);

        $targetUrl = $redirectUrl;

        $domain = $request->getUri()->getHost();

        $cookies = [
            $this->buildSessionCookie($sessionId, $domain)
        ];

        if ($validationCookie) {
            $cookies[] = $this->cookieHeaderGenerator->generateCookie(
                $validationCookie,
                1,
                ['domain' => $domain]
            );
        }

        if (!$isTokenAlready) {
            $targetUrl = $request->getUri()
                ->withHost($redirectUrlParts['host'])
                ->withPort($redirectUrlParts['port'] ?? null)
                ->withScheme($redirectUrlParts['scheme'])
                ->withQuery(http_build_query([
                    'redirect' => $redirectUrl,
                    'token' => $this->sessionStorage->createToken($sessionId),
                    'validation_cookie' => $validationCookie
                ]));
        }

        return $this->createResponse(
            302,
            self::NO_CACHE_HEADERS + [
                'Location' => (string)$targetUrl,
                'Set-Cookie' => $cookies
            ],
            ''
        );
    }

    private function isRedirectRequest(ServerRequestInterface $request): bool
    {
        return !empty($request->getQueryParams()['redirect']);
    }
}
