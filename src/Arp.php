<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Arp.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.2
 */
class Arp
{
    private Curl $curl;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * Retrieve a specific ARP entry or all ARP entries for a device.
     *
     * @see https://docs.librenms.org/API/ARP/#list_arp
     */
    public function get(string $ip, string $cidr): ?array
    {
        $url = $this->curl->getApiUrl("/resources/ip/arp/$ip/$cidr");
        $result = $this->curl->get($url);
        if (!isset($result['arp']) || (!count($result['arp']) > 0)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $result['arp'];
    }
}
