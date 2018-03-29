<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace EcomDev\CrossDomainSessionManager;

use PHPUnit\Framework\TestCase;

class SessionStorageTest extends TestCase
{
    const VALID_SESSION_REGEXP = '/^[0-9a-fA-F]{32}$/';
    /**
     * @var SessionStorage
     */
    private $storage;

    protected function setUp()
    {
        $this->storage = new SessionStorage();
    }

    /** @test */
    public function createsUniqueNewSessionIdentifier()
    {
        $sessionId = $this->storage->createSession();
        $this->assertRegExp(self::VALID_SESSION_REGEXP, $sessionId);
        $this->assertNotEquals($sessionId, $this->storage->createSession());
    }

    /** @test */
    public function validatesPreviouslyCreatedSession()
    {
        $sessionId = $this->storage->createSession();
        $this->assertEquals($sessionId, $this->storage->validateSession($sessionId));
    }

    /** @test */
    public function createsNewSessionWhenValidationFails()
    {
        $oldSessionId = 'somerandomsessionidthatisnotcorrect';
        $sessionId = $this->storage->validateSession($oldSessionId);
        $this->assertNotEquals($oldSessionId, $sessionId);
        $this->assertRegExp(self::VALID_SESSION_REGEXP, $sessionId);
    }

    /** @test */
    public function createsOnetimeTokenForSessionRetrieval()
    {
        $sessionId = $this->storage->createSession();
        $token = $this->storage->createToken($sessionId);
        $this->assertThat(
            $token,
            $this->logicalAnd(
                $this->logicalNot($this->equalTo($sessionId)),
                $this->matchesRegularExpression(self::VALID_SESSION_REGEXP)
            )
        );
    }

    /** @test */
    public function allowsRetrievalOfOriginalSessionIdByToken()
    {
        $sessionId = $this->storage->createSession();
        $token = $this->storage->createToken($sessionId);

        $this->assertEquals($sessionId, $this->storage->resolveToken($token));
    }

    /** @test */
    public function createsDummySessionIfTokenWasAlreadyResolved()
    {
        $sessionId = $this->storage->createSession();
        $token = $this->storage->createToken($sessionId);
        $this->storage->resolveToken($token);

        $this->assertThat(
            $this->storage->resolveToken($token),
            $this->logicalAnd(
                $this->logicalNot($this->equalTo($sessionId)),
                $this->matchesRegularExpression(self::VALID_SESSION_REGEXP)
            )
        );
    }
}
