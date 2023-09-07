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

        return $cache->get(Cache::PORT_KEY.$device_id);
    }

    public static function set(int $device_id, array|null $ports)
    {
        $cache = Cache::getInstance();

        if (!isset($pool)) {
            // @codeCoverageIgnoreStart
            $cache->delete(Cache::PORT_KEY.$device_id);
            // @codeCoverageIgnoreEnd
        }

        $cache->set(Cache::PORT_KEY.$device_id, $ports);
    }
}
