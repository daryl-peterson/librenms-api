<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Device.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Device
{
    private ApiClient $api;
    private array $list;
    private string $fileName;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;

        $dir = sys_get_temp_dir();
        $this->fileName = $dir.'/device-list.txt';
        $this->list = [];
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
        $url = $this->api->getApiUrl('/devices');

        $response = $this->api->post($url, $device);
        if (!isset($response) || !isset($response['devices'])) {
            return null;
        }

        return $response['devices'];
    }

    /**
     * Get device list.
     */
    public function listing(bool $force = false): ?array
    {
        if (!$force) {
            if (isset($this->list) & is_array($this->list)) {
                if (count($this->list) > 0) {
                    return $this->list;
                }
            }

            if (file_exists($this->fileName)) {
                $mtime = filemtime($this->fileName) + 3600;
                $ctime = time();

                if ($mtime > $ctime) {
                    $this->list = (array) unserialize(file_get_contents($this->fileName));
                }

                if (count($this->list) > 0) {
                    return $this->list;
                }
            }
        }

        $url = $this->api->getApiUrl('/devices/');
        $response = $this->api->get($url);
        if (!$response) {
            return null;
        }

        if (!isset($response['devices'])) {
            return null;
        }

        $result = [];
        $result['org'] = $response['devices'];
        $result['id'] = [];
        $result['ip'] = [];
        $result['host'] = [];

        foreach ($response['devices'] as $key => $device) {
            $result['id']['dev-'.$device->device_id] = $key;
            $result['ip'][$device->ip] = $key;
            $result['host'][$device->hostname] = $key;
        }

        $this->list = $result;

        file_put_contents($this->fileName, serialize($this->list));

        return $this->list;
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
        $url = $url = $this->api->getApiUrl("/devices/$hostname");
        $response = $this->api->delete($url);
        if (!isset($response) || !isset($response['devices'])) {
            return null;
        }

        return $response['devices'];
    }

    /**
     * Get device sensors.
     */
    public function sensor(int|string $hostname): ?array
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
        $url = $this->api->getApiUrl('/devices/'.$hostname);
        $result = $this->api->get($url);

        if (!isset($result)) {
            return null;
        }

        if (!isset($result['devices'][0])) {
            return null;
        }

        return $result['devices'][0];
    }

    /**
     * Get device by IP Address.
     */
    public function getByIp(string $ip): ?\stdClass
    {
        $ip = $this->getIpField($ip);
        $list = $this->listing();
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
     * Get device availability.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#availability
     */
    public function availability(int|string $hostname): ?array
    {
        $url = $this->api->getApiUrl("/devices/$hostname/availability");
        $result = $this->api->get($url);

        if (!isset($result) || !isset($result['availability'])) {
            return null;
        }

        return $result['availability'];
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
        $url = $this->api->getApiUrl("/devices/$hostname/discover");
        $result = $this->api->get($url);

        if (!isset($result['result']) || !isset($result['code'])) {
            return false;
        }

        if (200 !== $result['code']) {
            return false;
        }

        return true;
    }

/*

title: optional - Some title for the Maintenance
Will be replaced with hostname if omitted
notes: optional - Some description for the Maintenance
Will also be added to device notes if user prefs "Add schedule notes to devices notes" is set
start: optional - start time of Maintenance in full format Y-m-d H:i:00
eg: 2022-08-01 22:45:00
Current system time now() will be used if omitted
duration: required - Duration of Maintenance in format H:i / Hrs:Mins

*/

    /**
     * Undocumented function.
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

        $url = $this->api->getApiUrl("/devices/$hostname/maintenance");

        $result = $this->api->post($url, $data);

        if (!isset($result['result']) || !isset($result['code'])) {
            return false;
        }

        if (200 !== $result['code']) {
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
    public function ports(int|string $hostname): ?array
    {
        return $this->api->port->getByDevice($hostname);
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
    public function ipList(int|string $hostname): ?array
    {
        $url = $this->api->getApiUrl("/devices/$hostname/ip");
        $result = $this->api->get($url);

        if (!isset($result['addresses'])) {
            return null;
        }

        return $result['addresses'];
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
    public function links(int|string $hostname): ?array
    {
        return $this->api->link->get($hostname);
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
    public function alerts(int|string $hostname): ?array
    {
        return $this->api->log->alert($hostname);
    }

    /**
     * Get device outages.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#outages
     */
    public function outages(int|string $hostname): ?array
    {
        $url = $this->api->getApiUrl("/devices/$hostname/outages");
        $result = $this->api->get($url);

        if (!isset($result['outages'])) {
            return null;
        }

        return $result['outages'];
    }

    /**
     * Get device events.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     */
    public function events(int|string $hostname): ?array
    {
        return $this->api->log->event($hostname);
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
        // /devices/:hostname/rename/:new_hostname

        $url = $this->api->getApiUrl("/devices/$hostname/rename/$new_name");
        $result = $this->api->patch($url);

        if (!isset($result['code'])) {
            return false;
        }
        if (200 !== $result['code']) {
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
