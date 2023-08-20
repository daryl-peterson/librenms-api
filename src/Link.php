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
class Link
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Listing of discovered devices CDP, LLDP.
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Switching/#list_links
     */
    public function listing(): ?array
    {
        $url = $this->api->getApiUrl('/resources/links');
        $result = $this->api->get($url);

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
        $url = $this->api->getApiUrl("/devices/$hostname/links");
        $result = $this->api->get($url);

        if (!isset($result['links'])) {
            return null;
        }

        return $result['links'];
    }
}
