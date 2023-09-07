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
    public const LOG_LEVEL = 'log_level';
    public const LOG_FILE = 'log_file';

    public const DEVICE_ID = 'LMS-API-Device-Id-';
    public const DEVICE_HOSTNAME = 'LMS-API-Hostname-';
    public const SENSOR_KEY = 'LMS-API-Sensor-';
    public const PORT_KEY = 'LMS-API-Port-';
    public const IFNAME_KEY = 'LMS-API-iFName-';

    private static $instance = null;
    private string $key = 'LibrenmsApiClient';
    private string $keyBase = 'LMS-API-';

    public ArrayCachePool $pool;

    private function __construct()
    {
        // @codeCoverageIgnoreStart
        if (!isset($GLOBALS[$this->key])) {
            $GLOBALS[$this->key] = [];
        }
        // @codeCoverageIgnoreEnd
        $this->pool = new ArrayCachePool();
    }

    // @codeCoverageIgnoreStart
    public function __destruct()
    {
        unset($GLOBALS[$this->key]);
    }
    // @codeCoverageIgnoreEnd

    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($GLOBALS[$this->key][$key])) {
            return $default;
        }

        return $GLOBALS[$this->key][$key];
    }

    public function set(string $key, mixed $value)
    {
        $GLOBALS[$this->key][$key] = $value;
    }

    public function delete(string $key)
    {
        if (key_exists($key, $GLOBALS[$this->key])) {
            unset($GLOBALS[$this->key][$key]);
        }
    }

    public function exists(string $key): bool
    {
        if (key_exists($key, $GLOBALS[$this->key])) {
            return true;
        }

        return false;
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
}
