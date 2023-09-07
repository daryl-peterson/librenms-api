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
    /**
     * Sensor listing.
     *
     * @return array|null Array of stdClass Objects
     */
    public function getListing(): ?array
    {
        $url = $this->curl->getApiUrl('/resources/sensors');
        $this->result = $this->curl->get($url);

        $result = (!isset($this->result['sensors']) || (0 === count($this->result['sensors']))) ? null : $this->result['sensors'];
        $this->debug('GET SENSOR LISTING', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'result' => $result,
        ]);

        return SensorCache::set($result);
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
        $device = $this->getDeviceOrException($hostname);
        $sensors = SensorCache::get($device->device_id);

        if (isset($sensors) && count($sensors) > 0) {
            return $sensors;
        }

        $this->getListing();
        $sensors = SensorCache::get($device->device_id);
        $result = (!isset($sensors)) ? null : $sensors;

        $this->debug('GET SENSOR', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'hostname' => $hostname,
            'result' => $result,
        ]);

        return $result;
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
