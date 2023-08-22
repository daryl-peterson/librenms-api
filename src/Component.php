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
 *
 * @todo Finish class
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

    public function add(int|string $hostname, string $type)
    {
        // /devices/:hostname/components/:type
        // curl -X POST -H 'X-Auth-Token: YOURAPITOKENHERE' https://librenms.org/api/v0/devices/localhost/components/APITEST
        $url = $this->curl->getApiUrl("/devices/$hostname/components/$type");
        $result = $this->curl->post($url);

        if (!isset($result['components'])) {
            return null;
        }
        if (!count($result['components']) > 0) {
            return null;
        }

        return $result['components'];
    }

    /**
     * Get a list of components for a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#get_components
     */
    public function getListing(int|string $hostname): ?array
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/components");
        $result = $this->curl->get($url);

        if (!isset($result['components'])) {
            return null;
        }
        if (!count($result['components']) > 0) {
            return null;
        }

        return $result['components'];
    }
}
