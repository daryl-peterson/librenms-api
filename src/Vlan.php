<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Vlan.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 * @see https://docs.librenms.org/API/Switching/
 */
class Vlan
{
    protected Curl $curl;
    public array|null $result;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
        $this->result = [];
    }

    /**
     * Get a list of all VLANs for a given device.
     *
     * @see https://docs.librenms.org/API/Switching/#get_vlans
     */
    public function get(int|string $hostname): ?array
    {
        $url = $this->curl->getApiUrl("devices/$hostname/vlans");
        $this->result = $this->curl->get($url);
        if (!isset($this->result['vlans']) || (0 === count($this->result['vlans']))) {
            return null;
        }

        return $this->result['vlans'];
    }

    /**
     * Get a list of all VLANs.
     *
     * @see https://docs.librenms.org/API/Switching/#list_vlans
     */
    public function getListing(): ?array
    {
        $url = $this->curl->getApiUrl('/resources/vlans');
        $this->result = $this->curl->get($url);
        if (!isset($this->result['vlans']) || (0 === count($this->result['vlans']))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['vlans'];
    }
}
