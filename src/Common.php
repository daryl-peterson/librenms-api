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
class Common
{
    protected Curl $curl;

    /**
     * Get device list by.
     *
     * Valid type
     * - all: All devices
     * - active: Only not ignored and not disabled devices
     * - ignored: Only ignored devices
     * - up: Only devices that are up
     * - down: Only devices that are down
     * - disabled: Disabled devices
     * - os: search by os type
     * - mac: search by mac address
     * - ipv4: search by IPv4 address
     * - ipv6: search by IPv6 address (compressed or uncompressed)
     * - location: search by location
     * - location_id: serach by locaiton_id
     * - hostname: search by hostname
     * - sysName: search by sysName
     * - display: search by display name
     * - device_id: exact match by device-id
     * - type: search by device type
     *
     * Query
     * - query: If searching by, then this will be used as the input
     *
     * @see https://docs.librenms.org/API/Devices/#list_devices
     */
    public function getDeviceBy(string $type, string $query = null): ?array
    {
        $params['type'] = $type;
        $params['query'] = $query;

        $suffix = http_build_query($params);
        $url = $this->curl->getApiUrl('/devices');
        $url .= "?$suffix";
        $result = $this->curl->get($url);

        if (!isset($result['devices']) ||
            !is_array($result['devices']) || (0 === count($result['devices']))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $result['devices'];
    }

    /**
     * Get device by id or hostname.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function getDevice(int|string $hostname): ?\stdClass
    {
        if (is_string($hostname)) {
            $device = $this->getDeviceBy('hostname', $hostname);
        } else {
            $device = $this->getDeviceBy('device_id', $hostname);
        }

        if (is_array($device)) {
            return $device[0];
        }

        return null;
    }

    /**
     * Get device links. CDP, LLDP.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|false Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Switching/#get_links
     */
    public function getDeviceLinks(int|string $hostname): false|array
    {
        $device = $this->getDevice($hostname);
        if (!isset($device)) {
            return false;
        }

        $url = $this->curl->getApiUrl("/devices/$device->device_id/links");
        $result = $this->curl->get($url);

        if (!isset($result['links']) || (0 === count($result['links']))) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return $result['links'];
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
    public function getDevicePorts(int|string $hostname, string $columns = null): ?array
    {
        $device = $this->getDevice($hostname);
        if (!isset($device)) {
            return null;
        }

        $cols = 'device_id,port_id,disabled,deleted,ignore,ifName,';
        $cols .= 'ifDescr,ifAlias,ifMtu,ifType,ifVlan,ifSpeed,ifOperStatus,';
        $cols .= 'ifAdminStatus,ifPhysAddress,ifInErrors,ifOutErrors,poll_time';

        if (!isset($columns)) {
            $columns = $cols;
        }

        $columns = urlencode($columns);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/ports?columns=".$columns);
        $result = $this->curl->get($url);

        if (!isset($result['ports']) || (0 === count($result['ports']))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $result['ports'];
    }
}
