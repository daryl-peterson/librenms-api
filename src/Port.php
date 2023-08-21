<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Ports.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Port
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Get port by id.
     *
     * @see https://docs.librenms.org/API/Ports/#get_port_info
     */
    public function get(int $id): ?\stdClass
    {
        $url = $this->api->getApiUrl("/ports/$id");
        $result = $this->api->get($url);

        if (!isset($result['port'][0])) {
            return null;
        }

        return $result['port'][0];
    }

    /**
     * Get port listing.
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Ports/#get_all_ports
     */
    public function getListing(string $columns = null): ?array
    {
        $default = 'device_id,port_id,deleted,ifName,ifDescr,ifAlias,ifMtu,ifType,ifSpeed,ifOperStatus,ifAdminStatus,ifPhysAddress,ifInErrors,ifOutErrors';
        if (!isset($columns)) {
            $columns = $default;
        }

        $columns = urlencode($columns);
        $url = $this->api->getApiUrl('/ports?columns='.$columns);
        $result = $this->api->get($url);

        if (!isset($result['ports'])) {
            return null;
        }

        return $result['ports'];
    }

    /**
     * Update a device port notes field in the devices_attrs database.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#update_device_port_notes
     */
    public function setNotes(int|string $hostname, int $port_id, string $note)
    {
        $url = $this->api->getApiUrl("/devices/$hostname/port/$port_id");
        $result = $this->api->patch($url, ['notes' => $note]);
        print_r($result);

        return $result;
    }

    /**
     * Get a list of ports for a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Devices/#get_port_graphs
     */
    public function getByDevice(int|string $hostname): ?array
    {
        $columns = urlencode('device_id,port_id,ifName,ifDescr,ifAlias,ifMtu,ifType,ifSpeed,ifOperStatus,ifAdminStatus,ifPhysAddress,ifInErrors,ifOutErrors');
        $url = $this->api->getApiUrl("/devices/$hostname/ports?columns=".$columns);
        $result = $this->api->get($url);

        if (!isset($result['ports'])) {
            return null;
        }

        if (0 === count($result['ports'])) {
            return null;
        }

        return $result['ports'];
    }

    /**
     * Undocumented function.
     *
     * @see https://docs.librenms.org/API/Ports/#search_ports
     */
    public function search()
    {
    }
}
