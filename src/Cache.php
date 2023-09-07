<?php

namespace LibrenmsApiClient;

use Cache\Adapter\PHPArray\ArrayCachePool;

/**
 * Class description.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       1.0.0
 */
class Cache
{
    public const LOG_LEVEL = 'LMS-API-Log-Level';
    public const LOG_FILE = 'LMS-API-Log-File';
    public const DEVICE_ID = 'LMS-API-Device-Id-';
    public const DEVICE_HOSTNAME = 'LMS-API-Hostname-';
    public const SENSOR_KEY = 'LMS-API-Sensor-';
    public const PORT_KEY = 'LMS-API-Port-';
    public const IFNAME_KEY = 'LMS-API-iFName-';
    private static $instance = null;

    public ArrayCachePool $pool;

    private function __construct()
    {
        // @codeCoverageIgnoreStart
        $this->pool = new ArrayCachePool();
        // @codeCoverageIgnoreEnd
    }

    public static function getInstance(): self
    {
        if (null == self::$instance) {
            // @codeCoverageIgnoreStart
            self::$instance = new Cache();
            // @codeCoverageIgnoreEnd
        }

        return self::$instance;
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->pool->get($key, $default);
    }

    public function set(string $key, $value): bool
    {
        return $this->pool->set($key, $value);
    }

    public function delete(string $key): bool
    {
        return $this->pool->delete($key);
    }
}
