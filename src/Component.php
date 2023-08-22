<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Component.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Component
{
    private ApiClient $api;
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;
    }

    /**
     * Create a new component of a type on a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#add_components
     */
    public function add(int|string $hostname, string $type): ?\stdClass
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/components/$type");
        $result = $this->curl->post($url);

        if (!isset($result['components'])) {
            return null;
        }

        return $result['components'];
    }

    /**
     * Edit an existing component on a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#edit_components
     */
    public function edit(int|string $hostname, int $component_id, array $data): bool
    {
        $obj = new \stdClass();
        $obj->$component_id = (object) $data;

        $url = $this->curl->getApiUrl("/devices/$hostname/components");
        $result = $this->curl->put($url, $obj);
        if (!isset($result['code'])) {
            return false;
        }

        return true;
    }

    /**
     * Delete an existing component on a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#delete_components
     */
    public function delete(int|string $hostname, int $component_id): bool
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/components/$component_id");
        $result = $this->curl->delete($url);
        if (!isset($result['code'])) {
            return false;
        }

        return true;
    }

    /**
     * Get a list of components for a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#get_components
     */
    public function getListing(int|string $hostname): ?\stdClass
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/components");
        $result = $this->curl->get($url);

        if (!isset($result['components'])) {
            return null;
        }

        return $result['components'];
    }
}
