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

    /** @var TimeManager */
    private $timeManager;

    public function __construct(array $sessions = [], TimeManager $timeManager = null)
    {
        $this->timeManager = $timeManager ?: new RealTimeManager();
        $this->sessions = $this->transformSessionsFromArray($sessions);
    }

    public function createSession()
    {
        $sessionId = $this->randomHexString();
        $createTime = $this->timeManager->recordTime();

        $this->sessions[$sessionId] = [$createTime, $createTime];
        return $sessionId;
    }

    public function validateSession(string $sessionId)
    {
        if ($this->hasSession($sessionId)) {
            list($createTime,) = $this->sessions[$sessionId];
            $this->sessions[$sessionId] = [$createTime, $this->timeManager->recordTime()];
            return $sessionId;
        }

        return $this->createSession();
    }

    public function hasSession(string $sessionId)
    {
        return isset($this->sessions[$sessionId]);
    }

    public function createToken(string $sessionId)
    {
        $token = $this->randomHexString();
        $this->tokens[$token] = $this->validateSession($sessionId);
        return $token;
    }

    public function resolveToken(string $token)
    {
        $sessionId = $this->tokens[$token] ?? $this->createSession();
        unset($this->tokens[$token]);
        return $sessionId;
    }

    public function cleanExpired(int $lifeTime)
    {
        foreach ($this->tokens as $token => $sessionId) {
            if ($this->isSessionExpired($lifeTime, $sessionId)) {
                unset($this->tokens[$token]);
            }
        }

        foreach (array_keys($this->sessions) as $sessionId) {
            if ($this->isSessionExpired($lifeTime, $sessionId)) {
                unset($this->sessions[$sessionId]);
            }
        }
    }

    public function export()
    {
        $exportData = [];

        foreach ($this->sessions as $sessionId => list($createTime, $accessTime)) {
            $exportData[$sessionId] = [$createTime->format('r'), $accessTime->format('r')];
        }

        return $exportData;
    }

    private function transformSessionsFromArray(array $sessionData)
    {
        $sessions = [];
        foreach ($sessionData as $sessionId => list($createTime, $accessTime)) {
            $sessions[$sessionId] = [
                new \DateTimeImmutable($createTime),
                new \DateTimeImmutable($accessTime),
            ];
        }

        return $sessions;
    }

    private function isSessionExpired(int $lifeTime, string $sessionId)
    {
        list(, $accessTime) = $this->sessions[$sessionId];
        $isExpiredSession = $this->timeManager->isTimePassed($lifeTime, $accessTime);
        return $isExpiredSession;
    }

    private function randomHexString()
    {
        return bin2hex(random_bytes(16));
    }
}
