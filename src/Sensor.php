<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Sensors.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Sensor extends Common
{
    protected Curl $curl;
    private array $list;
    public array|null $result;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
        $this->list = [];
        $this->result = [];
    }

    /**
     * Sensor listing.
     *
     * @return array|null Array of stdClass Objects
     */
    public function getListing(): ?array
    {
        if (count($this->list) > 0) {
            return $this->list;
        }
        $url = $this->curl->getApiUrl('/resources/sensors');
        $this->result = $this->curl->get($url);

        if (!isset($this->result['sensors']) || (0 === count($this->result['sensors']))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $return = [];
        foreach ($this->result['sensors'] as $sensor) {
            $return[$sensor->device_id][] = $sensor;
        }
        $this->list = $return;

        return $this->list;
    }

    /**
     * Get sensors for device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     */
    public function get(int|string $hostname): ?array
    {
        $device = $this->getDevice($hostname);
        if (!isset($device)) {
            return null;
        }

        $sensors = $this->getListing();

        if (!isset($sensors[$device->device_id]) || (0 === count($sensors[$device->device_id]))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $sensors[$device->device_id];
    }

    /**
     * Get sensors by class.
     *
     * @return array|null Array of stdClass Objects
     */
    public function getByClass(string $class): ?array
    {
        $sensors = $this->getListing();
        if (!isset($sensors)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $result = [];
        foreach ($sensors as $device) {
            foreach ($device as $sensor) {
            }
            $deleted = $sensor->sensor_deleted;

            if ((0 !== $deleted) || ($sensor->sensor_class !== $class)) {
                continue;
            }

            $result[] = $sensor;
        }
        if (!count($result) > 0) {
            return null;
        }

        return $result;
    }
}
