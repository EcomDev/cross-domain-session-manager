<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace EcomDev\CrossDomainSessionManager;


class ServerSettingsFactory
{
    /** Default values for argv array */
    const DEFAULT_ARGV = [1 => null, 2 => null];

    public function createFromArray(array $data): ServerSettings
    {
        $data += [
            'bind' => '0.0.0.0:8080',
            'session_name' => 'sharedSession'
        ];

        $cookieOptions = [
            'lifetime' => (int)($data['cookie_lifetime'] ?? 3600),
            'path' => $data['cookie_path'] ?? '/',
            'http-only' => (bool)($data['cookie_http_only'] ?? true),
            'secure' => (bool)($data['cookie_secure'] ?? false),
        ];

        $optionalOptions = ['cookie_domain' => 'domain', 'cookie_trim_www' => 'trim-www'];

        foreach ($optionalOptions as $source => $target) {
            if (isset($data[$source])) {
                $cookieOptions[$target] = $data[$source];
            }
        }

        return new ServerSettings($data['bind'], $data['session_name'], $cookieOptions);
    }

    public function createFromArgv(array $argv)
    {
        list(,$bind, $sessionName) = $argv + self::DEFAULT_ARGV;

        $data = [];

        if ($bind) {
            $data['bind'] = $bind;
        }

        if ($sessionName) {
            $data['session_name'] = $sessionName;
        }

        return $this->createFromArray($data);
    }
}
