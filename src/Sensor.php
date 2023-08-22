<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Sensor.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Sensor
{
    private ApiClient $api;
    private Curl $curl;
    private string $fileName;
    private array $list;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;

        $dir = sys_get_temp_dir();
        $this->fileName = $dir.'/sensor-list.txt';
        $this->list = [];
    }

    /**
     * Sensor listing.
     *
     * @return array|null Array of stdClass Objects
     */
    public function getListing(bool $force = false): ?array
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

        $url = $this->curl->getApiUrl('/resources/sensors');
        $result = $this->curl->get($url);
        if (!isset($result['sensors'])) {
            return null;
        }

        if (0 === count($result['sensors'])) {
            return null;
        }

        $return = [];

        foreach ($result['sensors'] as $sensor) {
            $return[$sensor->device_id][] = $sensor;
        }
        file_put_contents($this->fileName, serialize($return));
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
    public function get(int|string $hostname, bool $force = false): ?array
    {
        $device = $this->api->device->get($hostname);
        if (!$device) {
            return null;
        }

        $sensors = $this->getListing($force);

        if (!isset($sensors[$device->device_id])) {
            return null;
        }

        if (0 === count($sensors[$device->device_id])) {
            return null;
        }

        return $sensors[$device->device_id];
    }

    /**
     * Get sensors by class.
     *
     * @return array|null Array of stdClass Objects
     */
    public function getByClass(string $class, bool $force = false): ?array
    {
        $sensors = $this->getListing($force);

        print_r($sensors);

        if (!isset($sensors)) {
            return null;
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

        return $result;
    }
}
