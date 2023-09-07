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
class SensorCache
{
    public static function get(int $device_id): ?array
    {
        $cache = Cache::getInstance();
        $pool = $cache->pool;

        return $pool->get(Cache::SENSOR_KEY.$device_id);
    }

    public static function set(array|null $list): ?array
    {
        if (!isset($list)) {
            return null;
        }

        $cache = Cache::getInstance();
        $pool = $cache->pool;

        $return = [];
        foreach ($list as $sensor) {
            $return[$sensor->device_id][] = $sensor;
        }
        foreach ($return as $device_id => $sensors) {
            $key = Cache::SENSOR_KEY.$device_id;
            $pool->set($key, $sensors);
        }

        return $return;
    }

    public static function delete(int $device_id)
    {
        $cache = Cache::getInstance();
        $pool = $cache->pool;

        $pool->delete(Cache::SENSOR_KEY.$device_id);
    }
}
