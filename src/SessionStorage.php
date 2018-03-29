<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;

class SessionStorage
{
    private $sessions = [];

    private $tokens = [];

    public function createSession()
    {
        $sessionId = $this->randomHexString();
        $this->sessions[$sessionId] = [];
        return $sessionId;
    }

    public function validateSession(string $sessionId)
    {
        return (isset($this->sessions[$sessionId]) ? $sessionId : $this->createSession());
    }

    public function createToken($sessionId)
    {
        $token = $this->randomHexString();
        $this->tokens[$token] = $this->validateSession($sessionId);
        return $token;
    }

    public function resolveToken($token)
    {
        $sessionId = $this->tokens[$token] ?? $this->createSession();
        unset($this->tokens[$token]);
        return $sessionId;
    }

    private function randomHexString()
    {
        return bin2hex(random_bytes(16));
    }
}
