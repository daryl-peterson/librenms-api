<?php

namespace LibrenmsApiClient;

/**
 * Device Links CDP, LLDP ect.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.2
 */
class Link
{
    private ApiClient $api;
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;
    }

    /**
     * Listing of discovered devices CDP, LLDP.
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Switching/#list_links
     */
    public function getListing(): ?array
    {
        $url = $this->curl->getApiUrl('/resources/links');
        $result = $this->curl->get($url);

        if (!isset($result['links'])) {
            return null;
        }

        return $result['links'];
    }

    /**
     * Get device links. CDP, LLDP.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     */
    public function get(int|string $hostname)
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/links");
        $result = $this->curl->get($url);

        if (!isset($result['links'])) {
            return null;
        }

        return $result['links'];
    }
}
