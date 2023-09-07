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
class PortCache
{
    public static function get(int $device_id): ?array
    {
        $cache = Cache::getInstance();
        $pool = $cache->pool;

        $result = $pool->get(Cache::PORT_KEY.$device_id);

        return $result;
    }

    public static function set(int $device_id, array|null $ports)
    {
        $cache = Cache::getInstance();
        $pool = $cache->pool;

        if (!isset($pool)) {
            // @codeCoverageIgnoreStart
            $pool->delete(Cache::PORT_KEY.$device_id);
            // @codeCoverageIgnoreEnd
        }

        $pool->set(Cache::PORT_KEY.$device_id, $ports);
    }
}
