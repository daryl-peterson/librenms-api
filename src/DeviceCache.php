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
class DeviceCache
{
    public static function get(string $type, string $query = null): ?\stdClass
    {
        $cache = Cache::getInstance();
        $pool = $cache->pool;
        $result = null;
        if (('device_id' !== $type && 'hostname' !== $type) || !isset($query)) {
            return $result;
        }

        if ('hostname' === $type) {
            $deviceId = $pool->get(Cache::DEVICE_HOSTNAME.$query);
            if (isset($deviceId)) {
                $result = $pool->get(Cache::DEVICE_ID.$deviceId);
            }
        } else {
            $result = $pool->get(Cache::DEVICE_ID.$query);
        }

        return $result;
    }

    public static function set(array|null $list)
    {
        if (!isset($list)) {
            return;
        }
        $cache = Cache::getInstance();
        $pool = $cache->pool;
        foreach ($list as $device) {
            $pool->set(Cache::DEVICE_ID.$device->device_id, $device);
            $pool->set(Cache::DEVICE_HOSTNAME.$device->hostname, $device->device_id);
        }
    }
}
