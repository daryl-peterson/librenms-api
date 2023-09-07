<?php

namespace LibrenmsApiClient;

use Psr\Log\LoggerAwareTrait;
use stdClass;

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
    use LoggerAwareTrait;

    protected Curl $curl;
    protected Cache $cache;
    public array|null $result;
    public string $portColumns;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
        $this->cache = Cache::getInstance();
        $this->result = [];
        $this->logger = new FileLogger();

        $this->portColumns = 'device_id,port_id,disabled,deleted,ignore,ifName,';
        $this->portColumns .= 'ifDescr,ifAlias,ifMtu,ifType,ifVlan,ifSpeed,ifOperStatus,';
        $this->portColumns .= 'ifAdminStatus,ifPhysAddress,ifInErrors,ifOutErrors,poll_time';
    }

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
        $device = DeviceCache::get($type, $query);
        if (isset($device) && is_object($device)) {
            return [$device];
        }

        $params['type'] = $type;
        $params['query'] = $query;

        $suffix = http_build_query($params);
        $url = $this->curl->getApiUrl('/devices');
        $url .= "?$suffix";
        $this->result = $this->curl->get($url);

        $result = (!isset($this->result['devices']) || (0 === count($this->result['devices']))) ? null : $this->result['devices'];
        DeviceCache::set($result);

        return $result;
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
        $this->result = $this->curl->get($url);

        $result = (!isset($this->result['links']) || (0 === count($this->result['links']))) ? null : $this->result['links'];

        return $result;
    }

    public function getDeviceIfNames(int|string $hostname): ?array
    {
        $device = $this->getDeviceOrException($hostname);
        $names = IfNamesCache::get($device->device_id);
        if (isset($names)) {
            return $names;
        }

        $url = $this->curl->getApiUrl("/devices/$device->device_id/ports?columns=ifName");
        $this->result = $this->curl->get($url);

        $result = (!isset($this->result['ports']) || (0 === count($this->result['ports']))) ? null : $this->result['ports'];
        $names = IfNamesCache::set($device->device_id, $result);

        return $names;
    }

    /**
     * Get a list of ports for a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Devices/#get_port_graphs
     *
     * @throws ApiException
     */
    public function getDevicePorts(int|string $hostname, string $columns = null): ?array
    {
        $device = $this->getDeviceOrException($hostname);
        $columns = $this->setPortColumns($columns);
        $ports = PortCache::get($device->device_id);
        if (isset($ports)) {
            return $ports;
        }

        $columns = urlencode($columns);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/ports?columns=".$columns);
        $this->result = $this->curl->get($url);

        $result = (!isset($this->result['ports']) || (0 === count($this->result['ports']))) ? null : $this->result['ports'];
        IfNamesCache::set($device->device_id, $result);
        PortCache::set($device->device_id, $result);

        return $result;
    }

    protected function getDeviceOrException(int|string $hostname): \stdClass
    {
        $device = $this->getDevice($hostname);
        if (!isset($device)) {
            throw new ApiException(ApiException::ERR_DEVICE_NOT_EXIST);
        }

        return $device;
    }

    protected function hasDeviceException(int|string $hostname)
    {
        $device = $this->getDevice($hostname);
        if (isset($device)) {
            throw new ApiException(ApiException::ERR_DEVICE_DOES_EXIST);
        }
    }

    protected function hasFieldOrException(\stdClass $object, string $field)
    {
        $fieldList = get_object_vars($object);
        if (!key_exists($field, $fieldList)) {
            throw new ApiException(ApiException::ERR_INVALID_FIELD);
        }
    }

    protected function setPortColumns(string $columns = null)
    {
        if (!isset($columns)) {
            $columns = $this->portColumns;
        }

        if (!str_contains($columns, 'ifName')) {
            $columns .= ',ifName';
        }

        return $columns;
    }

    protected function debug(string $message, array $context)
    {
        if (!isset($this->logger)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        $this->logger->debug($message, $context);
    }
}
