<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Inventor.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.2
 */
class Inventory extends Common
{
    protected Curl $curl;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * Get device inventory listing.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Inventory/#get_inventory_for_device
     *
     * @throws ApiException
     */
    public function getListing(int|string $hostname): ?array
    {
        $device = $this->getDeviceOrException($hostname);
        $url = $this->curl->getApiUrl("/inventory/$device->device_id/all");
        $this->result = $this->curl->get($url);

        return (!isset($this->result['inventory'])) ? null : $this->result['inventory'];
    }

    /**
     * Get device inventory.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Inventory/#get_inventory
     *
     * @throws ApiException
     */
    public function get(int|string $hostname)
    {
        $device = $this->getDeviceOrException($hostname);
        $url = $this->curl->getApiUrl("/inventory/$device->device_id");
        $this->result = $this->curl->get($url);

        return (!isset($this->result['inventory'])) ? null : $this->result['inventory'];
    }

    /**
     * Get list of hardware types.
     */
    public function getHardware(): ?array
    {
        return $this->getField('hardware');
    }

    /**
     * Get list of software versions.
     */
    public function getVersion(): ?array
    {
        return $this->getField('version');
    }

    /**
     * Get list of features.
     */
    public function getFeature()
    {
        return $this->getField('features');
    }

    /**
     * Get a list of device types.
     */
    public function getType(): array
    {
        return $this->getField('type');
    }

    private function getField(string $field)
    {
        $devices = $this->getDeviceBy('all');

        $result = [];
        foreach ($devices as $device) {
            $item = trim($device->$field);

            if (empty($item) || in_array($item, $result)) {
                continue;
            }

            $result[] = $item;
        }

        sort($result);

        return (0 === count($result)) ? null : $result;
    }
}
