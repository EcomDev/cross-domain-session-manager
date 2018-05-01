<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class CookieHeaderGeneratorFactory
{
    /**
     * @var TimeManager
     */
    private $timeManager;

    public function __construct(TimeManager $timeManager)
    {
        $this->timeManager = $timeManager;
    }

    public function createGenerator()
    {
        return new CookieHeaderGenerator($this->timeManager, []);
    }

    public function createGeneratorWithOptions(array $options)
    {
        return new CookieHeaderGenerator($this->timeManager, $options);
    }
}
