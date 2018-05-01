<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class FakeTimeManager implements TimeManager
{
    private $currentTime;

    public function __construct(\DateTimeImmutable $currentTime)
    {
        $this->currentTime = $currentTime;
    }

    public function recordTime(): \DateTimeImmutable
    {
        return $this->currentTime;
    }

    public function isTimePassed(int $seconds, \DateTimeInterface $recordedTime)
    {
        $pastTime = $this->currentTime->sub(new \DateInterval(sprintf('PT%dS', $seconds)));
        return $pastTime >= $recordedTime;
    }

    public function passTime(int $seconds)
    {
        $this->currentTime = $this->currentTime->add(new \DateInterval(sprintf('PT%sS', $seconds)));
    }
}
