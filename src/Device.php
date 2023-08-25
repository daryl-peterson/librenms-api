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
class Device
{
    private ApiClient $api;
    private Curl $curl;
    public array|null $result;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;
        $this->result = [];
    }

    /**
     * Add device.
     *
     * @see https://docs.librenms.org/API/Devices/#add_device
     */
    public function add(array $device): array
    {
        $snmpVersions = ['v1', 'v2', 'v3'];

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

        if (isset($device['snmp_disable'])) {
            if ($device['snmp_disable']) {
                foreach ($icmpOnly as $keyName) {
                    unset($device[$keyName]);
                }
            }
        }

        if (isset($device['snmpver'])) {
            $ver = $device['snmpver'];

            if (!in_array($ver, $snmpVersions)) {
                throw new ApiException('Invalid snmp version [1v,v2c,v3]');
            }
        }

        $url = $this->curl->getApiUrl('/devices');
        $this->result = $this->curl->post($url, $device);
        if (!isset($this->result['devices']) || !is_array($this->result['devices'])) {
            return null;
        }

        return $this->result['devices'];
    }

    /**
     * Get device list.
     */
    public function getListing(): ?array
    {
        $url = $this->curl->getApiUrl('/devices/');
        $this->result = $this->curl->get($url);

        if (!isset($this->result['devices']) || !is_array($this->result['devices'])) {
            return null;
        }

        return $this->result['devices'];
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
        $url = $this->curl->getApiUrl("/devices/$hostname/fdb");
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
        $url = $this->curl->getApiUrl("/devices/$hostname");
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
        return $this->api->sensor->get($hostname);
    }

    /**
     * Get device by host.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function get(int|string $hostname): ?\stdClass
    {
        $url = $this->curl->getApiUrl('/devices/'.$hostname);
        $this->result = $this->curl->get($url);

        if (!isset($this->result['devices'][0]) || !is_object($this->result['devices'][0])) {
            return null;
        }

        return $this->result['devices'][0];
    }

    /**
     * Get device by IP Address.
     */
    public function getByIp(string $ip): ?\stdClass
    {
        $ip = $this->getIpField($ip);
        $list = $this->getListing();
        if (!isset($list)) {
            return null;
        }

        if (!isset($list['ip'][$ip])) {
            return null;
        }

        $key = $list['ip'][$ip];
        $device = $list['org'][$key];

        return $this->get($device->hostname);
    }

    /**
     * Check if the device supports SNMP.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function hasSNMP(int|string $hostname): bool
    {
        $device = $this->get($hostname);
        if (!isset($device->snmp_disable)) {
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
        return $this->api->wireless->hasWireless($hostname);
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
        $url = $this->curl->getApiUrl("/devices/$hostname/availability");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['availability'])) {
            return null;
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
        $url = $this->curl->getApiUrl("/devices/$hostname/discover");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['result']) || !isset($this->result['code'])) {
            return false;
        }

        if (200 !== $this->result['code']) {
            return false;
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
     * Get a list of ports for a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Devices/#get_port_graphs
     */
    public function getPorts(int|string $hostname): ?array
    {
        return $this->api->port->getByDevice($hostname);
    }

    /**
     * Get information about a particular port for a device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#get_port_stats_by_port_hostname
     */
    public function getPortStats(int|string $hostname, string $ifname): ?\stdClass
    {
        return $this->api->port->getStats($hostname, $ifname);
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
    public function getPortsByMac(string $search, string $filter = null)
    {
        return $this->api->port->getByMac($search, $filter);
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
        $url = $this->curl->getApiUrl("/devices/$hostname/ip");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['addresses'])) {
            return null;
        }

        return $this->result['addresses'];
    }

    /**
     * Get discovered devices CDP, LLDP.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Switching/#get_links
     */
    public function getLinks(int|string $hostname): ?array
    {
        return $this->api->link->getByHost($hostname);
    }

    /**
     * Get device alert log.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_alertlog
     */
    public function getAlerts(
        int|string $hostname = null,
        int $limit = null,
        int $start = null,
        string $from = null,
        string $to = null
    ): ?array {
        return $this->api->log->getAlertLogs($hostname, $limit, $start, $from, $to);
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
        $url = $this->curl->getApiUrl("/devices/$hostname/outages");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['outages'])) {
            return null;
        }

        return $this->result['outages'];
    }

    /**
     * Get device events.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     */
    public function getEvents(
        int|string $hostname = null,
        int $limit = null,
        int $start = null,
        string $from = null,
        string $to = null
    ): ?array {
        return $this->api->log->getEventLogs($hostname, $limit, $start, $from, $to);
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

    /**
     * Make sure the ip is formatted correctly.
     */
    private function getIpField(string $value): string
    {
        if (empty($value)) {
            return $value;
        }

        if (false !== strpos($value, '.')) {
            return $value;
        }

        return long2ip($value);
    }
}
