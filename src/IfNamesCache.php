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
class IfNamesCache
{
    public static function set(int $device_id, array|null $ports): array
    {
        $cache = Cache::getInstance();
        $pool = $cache->pool;

        if (!isset($ports)) {
            // @codeCoverageIgnoreStart
            $pool->delete(Cache::IFNAME_KEY.$device_id);

            return null;
            // @codeCoverageIgnoreEnd
        }

        foreach ($ports as $port) {
            $names[] = $port->ifName;
        }

        natcasesort($names);
        $pool->set(Cache::IFNAME_KEY.$device_id, $names);

        return $names;
    }

    public static function get(int $device_id): ?array
    {
        $cache = Cache::getInstance();
        $pool = $cache->pool;

        return $pool->get(Cache::IFNAME_KEY.$device_id);
    }
}
