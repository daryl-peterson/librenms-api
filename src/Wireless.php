<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Wireless.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 *
 * @todo unit test
 */
class Wireless
{
    private ApiClient $api;
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;
    }

    /**
     * Get a list of overall wireless graphs available.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     */
    public function getTypes(int|string $hostname): ?array
    {
        return $this->getWireless($hostname);
    }

    /**
     * Get a list of wireless graphs based on provided class.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     * @param string     $type     device_current, device_dbm, device_state,
     *                             device_temperature, device_voltage, device_processor,
     *                             device_storage, device_mempool
     *
     * @return array|null Array of stdClass Objects
     */
    public function getSensors(int|string $hostname, string $type): ?array
    {
        return $this->getWireless($hostname, $type);
    }

    /**
     * Get the wireless sensors information based on ID.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     */
    public function getValue(int|string $hostname, string $type, int $sensor_id): ?array
    {
        return $this->getWireless($hostname, $type, $sensor_id);
    }

    /**
     * Get a particular wireless class graph for a device.
     *
     * - If you provide a sensor_id as well then a single sensor graph will be provided.
     * - If no sensor_id value is provided then you will be sent a stacked wireless graph.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array ['type'=>'image/png','src'=>'image source']
     *
     * @see https://docs.librenms.org/API/Devices/#get_wireless_graph
     */
    public function getGraph(int|string $hostname, string $type, int $sensor_id = null): ?array
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/graphs/wireless");

        if (isset($type)) {
            $url .= "/$type";
        }
        if (isset($sensor_id)) {
            $url .= "/$sensor_id";
        }
        $response = $this->curl->get($url);

        if (!isset($response['image'])) {
            return null;
        }

        if (0 === count($response)) {
            return null;
        }

        return $response['image'];
    }

    /**
     * Check if the device has wireless.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function hasWireless(int|string $hostname): bool
    {
        $result = $this->getWireless($hostname);
        if (!isset($result)) {
            return false;
        }
        if (!count($result) > 0) {
            return false;
        }

        return true;
    }

    /**
     * This function allows to do three things:.
     *
     * - Get a list of overall wireless graphs available.
     * - Get a list of wireless graphs based on provided class.
     * - Get the wireless sensors information based on ID.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#list_available_wireless_graphs
     */
    private function getWireless(int|string $hostname, string $type = null, int $sensor_id = null): ?array
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/wireless");

        if (isset($type)) {
            $url .= "/$type";
        }
        if (isset($sensor_id)) {
            $url .= "/$sensor_id";
        }
        $response = $this->curl->get($url);

        if (!isset($response['graphs'])) {
            return null;
        }

        if (0 === count($response['graphs'])) {
            return null;
        }

        return $response['graphs'];
    }
}
