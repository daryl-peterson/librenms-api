<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Arp.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Arp
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Get arp list.
     *
     * list_arp => /api/v0/resources/ip/arp/{query}/{cidr?}
     */
    public function get(string $ip, string $cidr): ?array
    {
        $url = $this->api->getApiUrl("/resources/ip/arp/$ip/$cidr");
        $result = $this->api->get($url);
        if (!isset($result) || !isset($result['arp'])) {
            return null;
        }

        return $result['arp'];
    }
}
