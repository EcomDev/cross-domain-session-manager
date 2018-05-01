<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class SessionStorageFactory
{
    /**
     * @var TimeManager
     */
    private $timeManager;

    public function __construct(TimeManager $timeManager = null)
    {
        $this->timeManager = $timeManager ?? new RealTimeManager();
    }

    public function createEmpty()
    {
        return new SessionStorage([], $this->timeManager);
    }

    public function createFromFile($filePath)
    {
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
        }

        return new SessionStorage($data ?? [], $this->timeManager);
    }
}
