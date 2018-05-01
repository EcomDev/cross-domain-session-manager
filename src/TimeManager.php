<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;

interface TimeManager
{
    public function recordTime(): \DateTimeImmutable;

    public function isTimePassed(int $seconds, \DateTimeInterface $recordedTime);
}
