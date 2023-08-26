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
class Port
{
    private Curl $curl;
    private string $columns;

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
        $result = $this->curl->get($url);

        if (!isset($result['port'][0])) {
            return null;
        }

        return $result['port'][0];
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
        $result = $this->curl->get($url);

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
        $url = $this->curl->getApiUrl("/devices/$hostname/port/$port_id");
        $result = $this->curl->patch($url, ['notes' => $note]);
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
    public function getByDevice(int|string $hostname, string $columns = null): ?array
    {
        if (!isset($columns)) {
            $columns = $this->columns;
        }

        $columns = urlencode($columns);
        $url = $this->curl->getApiUrl("/devices/$hostname/ports?columns=".$columns);
        $result = $this->curl->get($url);

        if (!isset($result['ports'])) {
            return null;
        }

        if (0 === count($result['ports'])) {
            return null;
        }

        return $result['ports'];
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
        $result = $this->curl->get($url);
        if (!isset($result['ports'])) {
            return null;
        }

        return $result['ports'];
    }

    /**
     * Get information about a particular port for a device.
     *
     * @see https://docs.librenms.org/API/Devices/#get_port_stats_by_port_hostname
     */
    public function getStats(int|string $hostname, string $ifname): ?\stdClass
    {
        $ifname = urlencode($ifname);
        $url = $this->curl->getApiUrl("/devices/$hostname/ports/$ifname");
        $result = $this->curl->get($url);

        if (!isset($result['port'])) {
            return null;
        }

        return $result['port'];
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
        $result = $this->curl->get($url);

        if (!isset($result['addresses'])) {
            return null;
        }
        if (!count($result['addresses']) > 0) {
            return null;
        }

        return $result['addresses'];
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
        $result = $this->curl->get($url);

        return $result;
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
        $result = $this->curl->get($url);

        if (!isset($result['ports'])) {
            return null;
        }
        if (!count($result['ports']) > 0) {
            return null;
        }

        return $result['ports'];
    }
}
