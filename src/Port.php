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
    /**
     * Get port by id.
     *
     * @see https://docs.librenms.org/API/Ports/#get_port_info
     */
    public function get(int $id): ?\stdClass
    {
        $url = $this->curl->getApiUrl("/ports/$id");
        $this->result = $this->curl->get($url);

        $result = (!isset($this->result['port'][0]) || !is_object($this->result['port'][0])) ? null : $this->result['port'][0];

        $this->debug('PORT VALUE', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'ports' => $result,
        ]);

        return $result;
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
        $columns = urlencode($this->setPortColumns($columns));
        $url = $this->curl->getApiUrl('/ports?columns='.$columns);
        $this->result = $this->curl->get($url);

        $result = !isset($this->result['ports']) ? null : $this->result['ports'];
        $this->debug('PORT VALUE', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'ports' => $result,
        ]);

        return $result;
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

        $result = !isset($this->result['ports']) ? null : $this->result['ports'];
        $this->debug('PORT VALUE', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'search' => $search,
            'filter' => $filter,
            'ports' => $result,
        ]);

        return $result;
    }

    /**
     * Get information about a particular port for a device.
     *
     * @see https://docs.librenms.org/API/Devices/#get_port_stats_by_port_hostname
     *
     * @throws ApiException
     */
    public function getStats(int|string $hostname, string $ifname): ?\stdClass
    {
        $device = $this->getDeviceOrException($hostname);
        $list = (array) $this->getDeviceIfNames($device->device_id);

        if (!in_array($ifname, $list)) {
            return null;
        }

        $ifname = urlencode($ifname);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/ports/$ifname");
        $this->result = $this->curl->get($url);

        $result = !isset($this->result['port']) ? null : $this->result['port'];
        $this->debug('PORT STATS', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'hostname' => $hostname,
            'ifname' => $ifname,
            'ports' => $result,
        ]);

        return $result;
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
        try {
            $url = $this->curl->getApiUrl("/ports/$port_id/ip");
            $this->result = $this->curl->get($url);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            if (!str_contains($msg, 'does not have any')) {
                // @codeCoverageIgnoreStart
                throw new ApiException($msg);
                // @codeCoverageIgnoreEnd
            }
        }

        $result = !isset($this->result['addresses']) ? null : $this->result['addresses'];
        $this->debug('ADDRESS INFO', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'portid' => $port_id,
            'addresses' => $result,
        ]);

        return $result;
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
        $columns = urlencode($this->setPortColumns($columns));

        $url = $this->curl->getApiUrl("/ports/search/$search?columns=$columns");
        $this->result = $this->curl->get($url);
        $result = (!isset($this->result['ports']) || 0 === count($this->result['ports'])) ? null : $this->result['ports'];
        $this->debug('PORT SEARCH', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'search' => $search,
            'result' => $result,
        ]);

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
        $columns = urlencode($this->setPortColumns($columns));
        $url = $this->curl->getApiUrl("/ports/search/$fields/$search?columns=$columns");
        $this->result = $this->curl->get($url);

        $result = (!isset($this->result['ports']) || 0 === count($this->result['ports'])) ? null : $this->result['ports'];
        $this->debug('PORT SEARCH', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'search' => $search,
            'fields' => $fields,
            'result' => $result,
        ]);

        return $result;
    }
}
