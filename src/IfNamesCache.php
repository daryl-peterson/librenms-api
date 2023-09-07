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

        if (!isset($ports)) {
            // @codeCoverageIgnoreStart
            $cache->delete(Cache::IFNAME_KEY.$device_id);

            return null;
            // @codeCoverageIgnoreEnd
        }

        foreach ($ports as $port) {
            $names[] = $port->ifName;
        }

        natcasesort($names);
        $cache->set(Cache::IFNAME_KEY.$device_id, $names);

        return $names;
    }

    public static function get(int $device_id): ?array
    {
        $cache = Cache::getInstance();

        return $cache->get(Cache::IFNAME_KEY.$device_id);
    }
}
