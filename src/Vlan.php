<?php

namespace LibrenmsApiClient;

/**
 * Class description.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 * @see https://docs.librenms.org/API/Switching/
 *
 * @todo unit test
 */
class Vlan
{
    private ApiClient $api;
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;
    }

    /**
     * Get a list of all VLANs for a given device.
     *
     * @see https://docs.librenms.org/API/Switching/#get_vlans
     */
    public function get(int|string $hostname): ?array
    {
        $url = $this->curl->getApiUrl("devices/$hostname/vlans");
        $result = $this->curl->get($url);
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
    public function getListing(): ?array
    {
        $url = $this->curl->getApiUrl('/resources/vlans');
        $result = $this->curl->get($url);
        if (!isset($result) || !isset($result['vlans'])) {
            return null;
        }

        return $result['vlans'];
    }
}
