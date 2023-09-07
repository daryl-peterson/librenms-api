<?php

namespace LibrenmsApiClient;

use stdClass;

/**
 * LibreNMS API Device.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.3
 */
class Device extends Common
{
    public const KEY_DEVICE_LIST = 'device-list';

    public const GETBY_ALL = 'all';
    public const GETBY_ACTIVE = 'active';
    public const GETBY_UP = 'up';
    public const GETBY_DOWN = 'down';
    public const GETBY_DISABLED = 'disabled';
    public const GETBY_OS = 'os';
    public const GETBY_MAC = 'mac';
    public const GETBY_IPV4 = 'ipv4';
    public const GETBY_IPV6 = 'ipv6';
    public const GETBY_LOCATION = 'location';
    public const GETBY_LOCATION_ID = 'location_id';
    public const GETBY_HOSTNAME = 'hostname';
    public const GETBY_SYSNAME = 'sysName';
    public const GETBY_DEVICE_ID = 'device_id';
    public const GETBY_TYPE = 'type';

    private DeviceValidator $validator;

    public function __construct(Curl $curl, DeviceValidator $validator)
    {
        parent::__construct($curl);
        $this->validator = $validator;
    }

    /**
     * Add device.
     *
     * Device array
     * - hostname (required): device hostname or IP
     * - display: A string to display as the name of this device, defaults to hostname (or device_display_default setting).
     * - port: SNMP port (defaults to port defined in config).
     * - transport: SNMP protocol (defaults to transport defined in config).
     * - snmpver: SNMP version to use, v1, v2c or v3. Defaults to v2c.
     * - port_association_mode: method to identify ports: ifIndex (default), ifName, ifDescr, ifAlias
     * - poller_group: This is the poller_group id used for distributed poller setup. Defaults to 0.
     * - force_add: Set to true to force the device to be added regardless of it being able to respond to snmp or icmp.
     *
     * For SNMP v1 or v2c
     * - community: Required for SNMP v1 or v2c.
     *
     * For SNMP v3
     * - authlevel: SNMP authlevel (noAuthNoPriv, authNoPriv, authPriv).
     * - authname: SNMP Auth username
     * - authpass: SNMP Auth password
     * - authalgo: SNMP Auth algorithm (MD5, SHA) (SHA-224, SHA-256, SHA-384, SHA-512 if supported by your server)
     * - cryptopass: SNMP Crypto Password
     * - cryptoalgo: SNMP Crypto algorithm (AES, DES)
     *
     * For ICMP only
     * - snmp_disable: Boolean, set to true for ICMP only.
     * - os: OS short name for the device (defaults to ping).
     * - sysName: sysName for the device.
     * - hardware: Device hardware.
     *
     * @see https://docs.librenms.org/API/Devices/#add_device
     *
     * @throws ApiException
     */
    public function add(array $device): ?\stdClass
    {
        $this->validator->validate($device);

        $deviceNew = $this->getDevice($device['hostname']);
        if (isset($deviceNew)) {
            throw new ApiException(ApiException::ERR_DEVICE_DOES_EXIST);
        }

        return $this->doAdd($device);
    }

    // @codeCoverageIgnoreStart
    protected function doAdd(array $device): ?\stdClass
    {
        $url = $this->curl->getApiUrl('/devices');
        $this->result = $this->curl->post($url, $device);
        $result = (!isset($this->result['devices'][0]) || !is_object($this->result['devices'][0])) ? null : $this->result['devices'][0];

        return $result;
    }
    // @codeCoverageIgnoreEnd

    /**
     * Get device list.
     *
     * @see https://docs.librenms.org/API/Devices/#list_devices
     */
    public function getListing(): ?array
    {
        return $this->getDeviceBy(self::GETBY_ALL);
    }

    /**
     * Get a list of FDB entries associated with a device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Devices/#get_device_fdb
     *
     * @throws ApiException
     */
    public function getFbd(int|string $hostname): ?array
    {
        $device = $this->getDeviceOrException($hostname);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/fdb");
        $this->result = $this->curl->get($url);

        return (!isset($this->result['ports_fdb']) || 0 === count($this->result['ports_fdb'])) ? null : $this->result['ports_fdb'];
    }

    /**
     * Delete device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#del_device
     *
     * @throws ApiException
     */
    public function delete(int|string $hostname): bool
    {
        $device = $this->getDeviceOrException($hostname);

        return $this->doDelete($device);
    }

    // @codeCoverageIgnoreStart
    protected function doDelete(\stdClass $device): bool
    {
        $url = $this->curl->getApiUrl("/devices/$device->device_id");
        $this->result = $this->curl->delete($url);

        $result = (!isset($this->result['devices'])) ? null : true;

        return $result;
    }
    // @codeCoverageIgnoreEnd

    /**
     * Get device by id or hostname.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function get(int|string $hostname): ?\stdClass
    {
        return $this->getDevice($hostname);
    }

    /**
     * Get device availability.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects { duration, availability_perc }
     *
     * @see https://docs.librenms.org/API/Devices/#availability
     *
     * @throws ApiException
     */
    public function getAvailability(int|string $hostname): ?array
    {
        $device = $this->getDeviceOrException($hostname);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/availability");
        $this->result = $this->curl->get($url);

        return (!isset($this->result['availability'])) ? null : $this->result['availability'];
    }

    /**
     * Discover device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#discover_device
     */
    public function discover(int|string $hostname): bool
    {
        $device = $this->getDeviceOrException($hostname);

        $url = $this->curl->getApiUrl("/devices/$device->device_id/discover");
        $this->result = $this->curl->get($url);

        return (!isset($this->result['code']) || (200 !== $this->result['code'])) ? false : true;
    }

    // @codeCoverageIgnoreStart
    private function doDiscover()
    {
    }
    // @codeCoverageIgnoreEnd

    /**
     * Set a device into maintenance mode.
     *
     * @param int|string  $hostname Hostname can be either the device hostname or id
     * @param string      $duration Duration of Maintenance in format H:i / Hrs:Mins
     * @param string|null $title    Title for the Maintenance
     * @param string|null $notes    Description for the Maintenance
     * @param string|null $start    start time of Maintenance in full format Y-m-d H:i:00
     *
     * @see https://docs.librenms.org/API/Devices/#maintenance_device
     *
     * @throws ApiException
     */
    public function maintenance(
        int|string $hostname,
        string $duration,
        string $title = null,
        string $notes = null,
        string $start = null
    ): bool {
        $device = $this->getDeviceOrException($hostname);
        $data['duration'] = $duration;
        $data = ['title' => $title, 'notes' => $notes, 'start' => $start];
        foreach ($data as $key => $value) {
            if (isset($value)) {
                continue;
            }
            unset($data[$key]);
        }

        return $this->doMaintenance($device, $data);
    }

    // @codeCoverageIgnoreStart
    protected function doMaintenance(\stdClass $device, array $data): bool
    {
        $url = $this->curl->getApiUrl("/devices/$device->device_id/maintenance");
        $this->result = $this->curl->post($url, $data);

        $result = (!isset($this->result['code']) || ($this->result['code'] > 299)) ? false : true;

        return $result;
    }
    // @codeCoverageIgnoreEnd

    /**
     * Update device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#update_device_field
     *
     * @throws ApiException
     */
    public function update(int|string $hostname, string $field, mixed $value): bool
    {
        $device = $this->getDeviceOrException($hostname);
        $this->hasFieldOrException($device, $field);

        return $this->doUpdate($device, $field, $value);
    }

    // @codeCoverageIgnoreStart
    protected function doUpdate(\stdClass $device, string $field, mixed $value): bool
    {
        $data['field'] = $field;
        $data['data'] = $value;
        $url = $this->curl->getApiUrl("/devices/$device->device_id");
        $this->result = $this->curl->patch($url, $data);

        $result = (!isset($this->result['code']) || ($this->result['code'] > 299)) ? false : true;

        return $result;
    }
    // @codeCoverageIgnoreEnd

    /**
     * Get device ip addresses.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Devices/#get_device_ip_addresses
     *
     * @throws ApiException
     */
    public function getIpList(int|string $hostname): ?array
    {
        $device = $this->getDeviceOrException($hostname);

        $url = $this->curl->getApiUrl("/devices/$device->device_id/ip");
        $this->result = $this->curl->get($url);

        return (!isset($this->result['addresses'])) ? null : $this->result['addresses'];
    }

    /**
     * Get device outages.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#outages
     *
     * @return array|null Array of stdClass Objects
     *
     * @throws ApiException
     */
    public function getOutages(int|string $hostname): ?array
    {
        $device = $this->getDeviceOrException($hostname);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/outages");
        $this->result = $this->curl->get($url);

        return (!isset($this->result['outages'])) ? null : $this->result['outages'];
    }

    /**
     * Rename device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#rename_device
     *
     * @throws ApiException
     */
    public function rename(int|string $hostname, string $new_name): bool
    {
        $device = $this->getDeviceOrException($hostname);
        $this->hasDeviceException($new_name);

        return $this->doRename($device, $new_name);
    }

    // @codeCoverageIgnoreStart
    protected function doRename(\stdClass $device, string $new_name): bool
    {
        $new_name = urlencode($new_name);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/rename/$new_name");
        $this->result = $this->curl->patch($url);

        return (!isset($this->result['code']) || ($this->result['code'] > 299)) ? false : true;
    }
    // @codeCoverageIgnoreEnd
}
