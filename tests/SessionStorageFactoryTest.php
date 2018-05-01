<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


use PHPUnit\Framework\TestCase;

class SessionStorageFactoryTest extends TestCase
{
    /** @var SessionStorageFactory */
    private $sessionStorageFactory;

    protected function setUp()
    {
        $this->sessionStorageFactory  = new SessionStorageFactory();
    }

    /** @test */
    public function createsEmptySessionStorage()
    {
        $this->assertEquals(new SessionStorage(), $this->sessionStorageFactory->createEmpty());
    }

   /** @test */
    public function createsSessionStorageFromFile()
    {
        $this->assertEquals(
            new SessionStorage([
                "session_one" => ["Thu, 28 Jan 2010 00:00:00 +0000", "Thu, 28 Jan 2010 00:00:00 +0000"],
                "session_two" => ["Thu, 28 Jan 2010 00:00:00 +0000", "Thu, 28 Jan 2010 01:00:00 +0000"],
                "session_three" => ["Thu, 28 Jan 2010 02:00:00 +0000", "Thu, 28 Jan 2010 02:00:00 +0000"]
            ]),
            $this->sessionStorageFactory->createFromFile(__DIR__ . '/fixture/session-storage.json')
        );
    }

    /** @test */
    public function createsEmptySessionStorageIfFileIsNotReadable()
    {
        $this->assertEquals(
            new SessionStorage(),
            $this->sessionStorageFactory->createFromFile(__DIR__ . '/fixture/no-file.json')
        );
    }

    /** @test */
    public function createsEmptyStorageFileHasNotValidContent()
    {
        $this->assertEquals(
            new SessionStorage(),
            $this->sessionStorageFactory->createFromFile(__DIR__ . '/fixture/invalid-file.json')
        );
    }
}
