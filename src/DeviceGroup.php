<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Device Group.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.2
 */
class DeviceGroup
{
    private ApiClient $api;
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $this->api->curl;
    }

    /**
     * List all device groups.
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/DeviceGroups/#get_devicegroups
     */
    public function getListing(): ?array
    {
        $url = $this->curl->getApiUrl('/devicegroups');
        $result = $this->curl->get($url);
        if (!isset($result['groups'])) {
            return null;
        }

        return $result['groups'];
    }

    /**
     * Add a new device group.
     *
     * - Upon success, the ID of the new device group is returned and the HTTP response code is 201.
     *
     * @param string $name    The name of the device group
     * @param bool   $type    If not static then dynamic. Setting this to static requires that the devices input be provided
     * @param string $desc    Description of the device group
     * @param array  $rules   if not static. A set of rules to determine which devices should be included in this device group
     * @param array  $devices if static. A list of devices that should be included in this group. This is a static list of devices
     *
     * @see https://docs.librenms.org/API/DeviceGroups/#add_devicegroup
     */
    public function add(string $name, bool $static, string $desc, $rules, $devices)
    {
        // /devicegroups
        // post
    }

    public function getDevicesByGroup()
    {
    }
}
