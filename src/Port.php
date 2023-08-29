<?php

namespace LibrenmsApiClient;

use stdClass;

/**
 * LibreNMS API Port.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Port extends Common
{
    protected Curl $curl;
    private string $columns;
    public array|null $result;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
        $this->columns = 'device_id,port_id,disabled,deleted,ignore,ifName,';
        $this->columns .= 'ifDescr,ifAlias,ifMtu,ifType,ifVlan,ifSpeed,ifOperStatus,';
        $this->columns .= 'ifAdminStatus,ifPhysAddress,ifInErrors,ifOutErrors,poll_time';
    }

    /**
     * Get port by id.
     *
     * @see https://docs.librenms.org/API/Ports/#get_port_info
     */
    public function get(int $id): ?\stdClass
    {
        $url = $this->curl->getApiUrl("/ports/$id");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['port'][0])) {
            return null;
        }

        return $this->result['port'][0];
    }

    /**
     * Get info for all ports on all devices.
     *
     * - Strongly recommend that you use the columns parameter to avoid pulling too much data
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Ports/#get_all_ports
     */
    public function getListing(string $columns = null): ?array
    {
        if (!isset($columns)) {
            $columns = $this->columns;
        }

        $columns = urlencode($columns);
        $url = $this->curl->getApiUrl('/ports?columns='.$columns);
        $this->result = $this->curl->get($url);

        if (!isset($this->result['ports'])) {
            return null;
        }

        return $this->result['ports'];
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
        $device = $this->getDevice($hostname);
        if (!isset($device)) {
            return null;
        }
        $url = $this->curl->getApiUrl("/devices/$device->device_id/port/$port_id");
        $this->result = $this->curl->patch($url, ['notes' => $note]);
        print_r($this->result);

        return $this->result;
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
    public function getByDevice(int|string $hostname, string $columns = null): ?array
    {
        return $this->getDevicePorts($hostname, $columns);
    }

    /**
     * Search for ports matching the search mac.
     *
     * - Search a mac address in fdb and print the ports ordered by the mac count of the associated port
     *
     * @return array|\stdClass|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Ports/#ports_with_associated_mac
     */
    public function getByMac(string $search, string $filter = null): null|array|\stdClass
    {
        $url = $this->curl->getApiUrl("/ports/mac/$search");
        if (isset($filter)) {
            $url .= '?filter='.$filter;
        }
        $this->result = $this->curl->get($url);
        if (!isset($this->result['ports'])) {
            return null;
        }

        return $this->result['ports'];
    }

    /**
     * Get information about a particular port for a device.
     *
     * @see https://docs.librenms.org/API/Devices/#get_port_stats_by_port_hostname
     */
    public function getStats(int|string $hostname, string $ifname): ?\stdClass
    {
        $device = $this->getDevice($hostname);
        if (!isset($device)) {
            return null;
        }

        $ifname = urlencode($ifname);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/ports/$ifname");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['port'])) {
            return null;
        }

        return $this->result['port'];
    }

    /**
     * Get all IP info (v4 and v6) for a given port id.
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Ports/#get_port_ip_info
     */
    public function getIpInfo(int $port_id): ?array
    {
        $url = $this->curl->getApiUrl("/ports/$port_id/ip");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['addresses'])) {
            return null;
        }
        if (!count($this->result['addresses']) > 0) {
            return null;
        }

        return $this->result['addresses'];
    }

    /**
     * Search for ports matching the query.
     *
     * - Search string to search in fields: ifAlias, ifDescr, and ifName
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Ports/#search_ports
     */
    public function search(string $search, string $columns = null): ?array
    {
        $search = urlencode($search);
        if (!isset($columns)) {
            $columns = $this->columns;
        }
        $columns = urlencode($columns);

        $url = $this->curl->getApiUrl("/ports/search/$search?columns=$columns");
        $this->result = $this->curl->get($url);

        return $this->result;
    }

    /**
     * Specific search for ports matching the query.
     *
     * - Search: string to search in fields
     * - Fields: comma separated list of field(s) to search
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Ports/#search_ports-in-specific-fields
     */
    public function searchBy(string $search, string $fields = null, string $columns = null): ?array
    {
        $search = urlencode($search);
        if (!isset($fields)) {
            $fields = 'ifName,ifDescr,ifAlias';
        }
        $fields = urlencode($fields);
        if (!isset($columns)) {
            $columns = $this->columns;
        }
        $columns = urlencode($columns);
        $url = $this->curl->getApiUrl("/ports/search/$fields/$search?columns=$columns");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['ports'])) {
            return null;
        }
        if (!count($this->result['ports']) > 0) {
            return null;
        }

        return $this->result['ports'];
    }
}
