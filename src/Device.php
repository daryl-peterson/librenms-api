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

    protected Curl $curl;
    public array|null $result;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;

        $this->result = [];
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
     */
    public function add(array $device): array
    {
        $snmpVersions = ['v1', 'v2c', 'v3'];

        if (!isset($device['hostname'])) {
            return null;
        }

        $icmpOnly = [
            'port',
            'transport',
            'snmpver',
            'community',
            'authlevel',
            'authname',
            'authpass',
            'authalgo',
            'cryptopass',
            'cryptoalgo',
            'port_association_mode',
        ];

        if (isset($device['snmpver'])) {
            $ver = $device['snmpver'];

            if (!in_array($ver, $snmpVersions)) {
                throw new ApiException('Invalid snmp version [1v,v2c,v3]');
            }
        }

        if (isset($device['snmp_disable']) && (1 === $device['snmp_disable'])) {
            foreach ($icmpOnly as $keyName) {
                unset($device[$keyName]);
            }
        }

        $url = $this->curl->getApiUrl('/devices');
        $this->result = $this->curl->post($url, $device);
        if (!isset($this->result['devices']) || !is_array($this->result['devices'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['devices'];
    }

    /**
     * Get device list.
     *
     * @see https://docs.librenms.org/API/Devices/#list_devices
     */
    public function getListing(bool $force = false): ?array
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
     */
    public function getFbd(int|string $hostname): ?array
    {
        $device = $this->get($hostname);
        if (!isset($device)) {
            return null;
        }

        $url = $this->curl->getApiUrl("/devices/$device->device_id/fdb");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['ports_fdb']) || !count($this->result['ports_fdb']) > 0) {
            return null;
        }

        return $this->result['ports_fdb'];
    }

    /**
     * Delete device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#del_device
     */
    public function delete(int|string $hostname): ?array
    {
        $device = $this->get($hostname);
        if (!isset($device)) {
            return null;
        }
        $url = $this->curl->getApiUrl("/devices/$device->device_id");
        $this->result = $this->curl->delete($url);
        if (!isset($this->result['devices'])) {
            return null;
        }

        return $this->result['devices'];
    }

    /**
     * Get device sensors.
     */
    public function getSensors(int|string $hostname): ?array
    {
        return [];
        // return $this->api->sensor->get($hostname);
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
    public function getPorts(int|string $hostname, string $columns = null): ?array
    {
        return $this->getDevicePorts($hostname, $columns);
    }

    /**
     * Get device by id or hostname.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function get(int|string $hostname): ?\stdClass
    {
        return $this->getDevice($hostname);
    }

    public function getByIpV4(string $address): ?array
    {
        return $this->getDeviceBy('ipv4', $address);
    }

    /**
     * Check if the device supports SNMP.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function hasSNMP(int|string $hostname): bool
    {
        $device = $this->get($hostname);
        if (!isset($device) || (1 === $device->snmp_disable)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the device has wireless.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function hasWireless(int|string $hostname): bool
    {
        return false;
        // return $this->api->wireless->hasWireless($hostname);
    }

    /**
     * Get device availability.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects { duration, availability_perc }
     *
     * @see https://docs.librenms.org/API/Devices/#availability
     */
    public function getAvailability(int|string $hostname): ?array
    {
        $device = $this->get($hostname);
        if (!isset($device)) {
            return null;
        }
        $url = $this->curl->getApiUrl("/devices/$device->device_id/availability");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['availability'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['availability'];
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
        $device = $this->get($hostname);
        if (!isset($device)) {
            return false;
        }

        $url = $this->curl->getApiUrl("/devices/$device->device_id/discover");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['code']) || (200 !== $this->result['code'])) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

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
     */
    public function maintenance(
        int|string $hostname,
        string $duration,
        string $title = null,
        string $notes = null,
        string $start = null
    ): bool {
        $data['duration'] = $duration;

        if (isset($title)) {
            $data['title'] = $title;
        }
        if (isset($notes)) {
            $data['notes'] = $notes;
        }
        if (isset($start)) {
            $data['start'] = $start;
        }

        $url = $this->curl->getApiUrl("/devices/$hostname/maintenance");

        $this->result = $this->curl->post($url, $data);

        if (!isset($this->result['result']) || !isset($this->result['code'])) {
            return false;
        }

        if (200 !== $this->result['code']) {
            return false;
        }

        return true;
    }

    /**
     * Get device ip addresses.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Devices/#get_device_ip_addresses
     */
    public function getIpList(int|string $hostname): ?array
    {
        $device = $this->get($hostname);
        if (!isset($device)) {
            return null;
        }

        $url = $this->curl->getApiUrl("/devices/$device->device_id/ip");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['addresses'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['addresses'];
    }

    /**
     * Get device outages.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#outages
     *
     * @return array|null Array of stdClass Objects
     */
    public function getOutages(int|string $hostname): ?array
    {
        $device = $this->get($hostname);
        if (!isset($device)) {
            return null;
        }
        $url = $this->curl->getApiUrl("/devices/$device->device_id/outages");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['outages'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['outages'];
    }

    /**
     * Rename device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#rename_device
     */
    public function rename(int|string $hostname, string $new_name): bool
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/rename/$new_name");
        $this->result = $this->curl->patch($url);

        if (!isset($this->result['code'])) {
            return false;
        }
        if (200 !== $this->result['code']) {
            return false;
        }

        return true;
    }
}
