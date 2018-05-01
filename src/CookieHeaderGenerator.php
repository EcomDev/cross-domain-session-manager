<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class CookieHeaderGenerator
{
    /**
     * @var TimeManager
     */
    private $timeManager;
    /**
     * @var array
     */
    private $defaultOptions;

    public function __construct(TimeManager $timeManager, array $defaultOptions = [])
    {
        $this->timeManager = $timeManager;
        $this->defaultOptions = $defaultOptions;
    }

    public function generateCookie($name, $value, array $options = [])
    {
        $optionParts = $this->generateCookiePartsFromOptions($options);

        array_unshift($optionParts, sprintf('%s=%s', urlencode($name), urlencode($value)));

        return implode('; ', $optionParts);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function generateCookiePartsFromOptions(array $options): array
    {
        $options = $this->substituteSubdomainWithBaseOne($options);

        $options += $this->defaultOptions;

        $options = $this->trimWwwInDomainNameForCookie($options);

        if (isset($options['lifetime'])) {
            $date = $this->timeManager->recordTime();
            $lifetime = new \DateInterval(sprintf('PT%dS', (int)$options['lifetime']));
            $options['expires'] = $date->add($lifetime)->format('r');
            $options['max-age'] = $lifetime->format('%s');
        }

        $headerOptionMap = [
            'expires' => 'Expires',
            'max-age' => 'Max-Age',
            'path' => 'Path',
            'domain' => 'Domain',
            'http-only' => 'HttpOnly',
            'secure' => 'Secure'
        ];

        $parts = [];

        foreach ($headerOptionMap as $partName => $headerName) {
            if (isset($options[$partName]) && !is_bool($options[$partName])) {
                $parts[] = sprintf('%s=%s', $headerName, $options[$partName]);
            } elseif (isset($options[$partName]) && $options[$partName] === true) {
                $parts[] = sprintf('%s', $headerName);
            }
        }

        return $parts;
    }

    private function substituteSubDomainWithBaseOne(array $options): array
    {
        $baseDomain = $this->defaultOptions['domain'] ?? null;
        $subDomain = $options['domain'] ?? null;

        if (!$baseDomain || !$subDomain || strlen($subDomain) <= strlen($baseDomain)) {
            return $options;
        }

        $startsWithBaseDomain = substr($subDomain, -strlen('.' . $baseDomain)) === ('.' . $baseDomain);
        $options['domain'] = $startsWithBaseDomain ? $baseDomain : $subDomain;

        return $options;
    }

    private function trimWwwInDomainNameForCookie(array $options): array
    {
        if (isset($options['trim-www']) && substr($options['domain'], 0, 4) === 'www.') {
            $options['domain'] = substr($options['domain'], 4);
        }

        return $options;
    }
}
