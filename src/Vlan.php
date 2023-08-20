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
 * @since       0.0.1
 * @see https://docs.librenms.org/API/Switching/
 */
class Vlan
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Get a list of all VLANs for a given device.
     *
     * @see https://docs.librenms.org/API/Switching/#get_vlans
     */
    public function get(int|string $hostname): ?array
    {
        $url = $this->api->getApiUrl("devices/$hostname/vlans");
        $result = $this->api->get($url);
        if (!isset($result) || !isset($result['vlans'])) {
            return null;
        }

        return $result['vlans'];
    }

    /**
     * Get a list of all VLANs.
     *
     * @see https://docs.librenms.org/API/Switching/#list_vlans
     */
    public function listing(): ?array
    {
        $url = $this->api->getApiUrl('/resources/vlans');
        $result = $this->api->get($url);
        if (!isset($result) || !isset($result['vlans'])) {
            return null;
        }

        return $result['vlans'];
    }
}
