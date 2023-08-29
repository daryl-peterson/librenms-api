<?php

namespace LibrenmsApiClient;

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
    private static $instance = null;
    private string $key = 'LibrenmsApiClient';

    private function __construct()
    {
        if (!isset($GLOBALS[$this->key])) {
            $GLOBALS[$this->key] = [];
        }
    }

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
            self::$instance = new Cache();
        }

        return self::$instance;
    }
}
