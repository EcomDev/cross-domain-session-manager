<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;

use PHPUnit\Framework\TestCase;

class RealTimeManagerTest extends TestCase
{
    /** @var RealTimeManager */
    private $timeManager;

    protected function setUp()
    {
        $this->timeManager = new RealTimeManager();
    }

    /** @test */
    public function recordsCurrentTime()
    {
        $firstTime = $this->timeManager->recordTime();
        $secondTime = $this->timeManager->recordTime();


        $this->assertThat($firstTime, $this->logicalAnd(
            $this->isInstanceOf(\DateTimeInterface::class),
            $this->lessThan($secondTime)
        ));
    }

    /** @test */
    public function returnsTrueWhenTimeHasPassedSinceCurrentTime()
    {
        $this->assertTrue(
            $this->timeManager->isTimePassed(
                3600,
                new \DateTimeImmutable('-1 hour')
            )
        );
    }

    /** @test */
    public function returnsFalseWhenTimeHasNotPassedSinceCurrentTime()
    {
        $this->assertFalse(
            $this->timeManager->isTimePassed(
                3600,
                new \DateTimeImmutable('-59 minutes')
            )
        );
    }
}
