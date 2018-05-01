<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;

use PHPUnit\Framework\TestCase;

class FakeTimeManagerTest extends TestCase
{
    const CURRENT_TIME = '2010-01-28T15:00:00+02:00';
    const CURRENT_TIME_MINUS_ONE_HOUR = '2010-01-28T14:00:00+02:00';
    const CURRENT_TIME_MINUS_30_MINUTES = '2010-01-28T14:30:00+02:00';
    const CURRENT_TIME_PLUS_ONE_MINUTE = '2010-01-28T15:01:00+02:00';

    /** @test */
    public function returnsSameTimeAsTimeRecordedInConstructor()
    {
        $timeManager = new FakeTimeManager(new \DateTimeImmutable(self::CURRENT_TIME));

        $this->assertEquals(new \DateTimeImmutable(self::CURRENT_TIME), $timeManager->recordTime());
    }

    /** @test */
    public function allowsToManuallyPassTimeInSeconds()
    {
        $timeManager = new FakeTimeManager(new \DateTimeImmutable(self::CURRENT_TIME));

        $timeManager->passTime(60);

        $this->assertEquals(new \DateTimeImmutable(self::CURRENT_TIME_PLUS_ONE_MINUTE), $timeManager->recordTime());
    }

    /** @test */
    public function returnsTrueWhenTimeHasPassedSinceCurrentTime()
    {
        $timeManager = new FakeTimeManager(new \DateTimeImmutable(self::CURRENT_TIME));

        $this->assertTrue(
            $timeManager->isTimePassed(
                3600,
                new \DateTimeImmutable(self::CURRENT_TIME_MINUS_ONE_HOUR)
            )
        );
    }

    /** @test */
    public function returnsFalseWhenTimeHasNotPassedSinceCurrentTime()
    {
        $timeManager = new FakeTimeManager(new \DateTimeImmutable(self::CURRENT_TIME));

        $this->assertFalse(
            $timeManager->isTimePassed(
                3600,
                new \DateTimeImmutable(self::CURRENT_TIME_MINUS_30_MINUTES)
            )
        );
    }
}
