<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class RealTimeManager implements TimeManager
{
    public function recordTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    public function isTimePassed(int $seconds, \DateTimeInterface $recordedTime)
    {
        $currentTime = $this->recordTime();
        $pastTime = $currentTime->sub(new \DateInterval(sprintf('PT%dS', $seconds)));
        return $pastTime >= $recordedTime;
    }
}
