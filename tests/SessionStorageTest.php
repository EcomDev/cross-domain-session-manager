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
    const FAKE_TIME_REFERENCE = 'Thu, 28 Jan 2010 00:00:00 +0000';
    const FAKE_TIME_REFERENCE_PLUS_ONE_HOUR = 'Thu, 28 Jan 2010 01:00:00 +0000';
    const FAKE_TIME_REFERENCE_PLUS_TWO_HOURS = 'Thu, 28 Jan 2010 02:00:00 +0000';
    /**
     * @var SessionStorage
     */
    private $storage;

    /** @var FakeTimeManager */
    private $timeManager;

    protected function setUp()
    {
        $this->timeManager = new FakeTimeManager(new \DateTimeImmutable(self::FAKE_TIME_REFERENCE));
        $this->storage = new SessionStorage([], $this->timeManager);
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
    public function returnsFalseIfSessionIsNotRegisteredOnCheck()
    {
        $this->assertFalse($this->storage->hasSession('somerandomsessionid'));
    }

    /** @test */
    public function returnsTrueIfSessionIsRegistered()
    {
        $sessionId = $this->storage->createSession();
        $this->assertTrue($this->storage->hasSession($sessionId));
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

    /** @test */
    public function clearsSessionThatHasBeenCreatesBeforeExpireTime()
    {
        $oldSession = $this->storage->createSession();
        $this->timeManager->passTime(3600);
        $newSession = $this->storage->createSession();
        $this->storage->cleanExpired(3600);

        $this->assertFalse($this->storage->hasSession($oldSession));
        $this->assertTrue($this->storage->hasSession($newSession));
    }

    /** @test */
    public function clearsRelatedTokensWhenSessionExpires()
    {
        $sessionId = $this->storage->createSession();
        $token = $this->storage->createToken($sessionId);
        $this->timeManager->passTime(3600);

        $this->storage->cleanExpired(3600);

        $this->assertNotEquals($sessionId, $this->storage->resolveToken($token));
    }

    /** @test */
    public function preservesRecentlyAccessedSessionViaTokenDuringExpiration()
    {
        $sessionId = $this->storage->createSession();
        $this->timeManager->passTime(1800);
        $token = $this->storage->createToken($sessionId);
        $this->timeManager->passTime(1800);

        $this->storage->cleanExpired(3600);

        $this->assertEquals($sessionId, $this->storage->resolveToken($token));
    }

    /** @test */
    public function preservesRecentlyAccessedSessionViaValidationDuringExpiration()
    {
        $oldSessionOne = $this->storage->createSession();
        $this->timeManager->passTime(1800);
        $this->storage->validateSession($oldSessionOne);
        $this->timeManager->passTime(1800);

        $this->storage->cleanExpired(3600);

        $this->assertTrue($this->storage->hasSession($oldSessionOne));
    }

    /** @test */
    public function exportsSessionData()
    {
        $sessionIdOne = $this->storage->createSession();
        $this->timeManager->passTime(3600);
        $sessionIdTwo = $this->storage->createSession();
        $this->timeManager->passTime(3600);
        $sessionIdThree = $this->storage->createSession();


        $this->assertEquals(
            [
                $sessionIdOne => [self::FAKE_TIME_REFERENCE, self::FAKE_TIME_REFERENCE],
                $sessionIdTwo => [self::FAKE_TIME_REFERENCE_PLUS_ONE_HOUR, self::FAKE_TIME_REFERENCE_PLUS_ONE_HOUR],
                $sessionIdThree => [self::FAKE_TIME_REFERENCE_PLUS_TWO_HOURS, self::FAKE_TIME_REFERENCE_PLUS_TWO_HOURS],
            ],
            $this->storage->export()
        );
    }

    /** @test */
    public function loadsDataFromArray()
    {
        $this->storage = new SessionStorage(
            [
                'session_one' => [self::FAKE_TIME_REFERENCE, self::FAKE_TIME_REFERENCE],
                'session_two' => [self::FAKE_TIME_REFERENCE_PLUS_ONE_HOUR, self::FAKE_TIME_REFERENCE_PLUS_ONE_HOUR],
                'session_three' => [self::FAKE_TIME_REFERENCE_PLUS_TWO_HOURS, self::FAKE_TIME_REFERENCE_PLUS_TWO_HOURS],
            ]
        );

        $this->assertTrue($this->storage->hasSession('session_one'));
        $this->assertTrue($this->storage->hasSession('session_two'));
        $this->assertTrue($this->storage->hasSession('session_three'));
    }
}
