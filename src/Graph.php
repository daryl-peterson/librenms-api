<?php

namespace LibrenmsApiClient;

use stdClass;

/**
 * LibreNMS API graphs.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.2
 */
class Graph extends Common
{
    /**
     * Get a list of available graphs for a device, this does not include ports.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects {"desc":"Poller Time","name":"device_poller_perf"}
     *
     * @see https://docs.librenms.org/API/Devices/#get_graphs
     */
    public function getTypes(int|string $hostname): ?array
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/graphs");
        $this->result = $this->curl->get($url);

        return (!isset($this->result['graphs']) || (0 === count($this->result['graphs']))) ? null : $this->result['graphs'];
    }

    /**
     * Get a specific graph for a device, this does not include ports.
     *
     * @param int|string  $hostname Hostname can be either the device hostname or id
     * @param string|null $output   Set how the graph should be outputted (base64, display), defaults to display
     *
     * @return array|null Array['type'=>'image/png','src'=>'raw image']
     *
     * @see https://docs.librenms.org/API/Devices/#get_graph_generic_by_hostname
     *
     * @throws ApiException
     */
    public function getByType(
        int|string $hostname,
        string $type,
        string $from = null,
        string $to = null,
        string $output = 'display'
    ): ?array {
        $device = $this->getDeviceOrException($hostname);
        $url = $this->curl->getApiUrl("/devices/$device->device_id/$type");
        $params = [];

        $this->debug('FIXING INTERFACE NAMES', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'from' => $from,
            'to' => $to,
            'output' => $output,
        ]);

        if (isset($from)) {
            $params['from'] = $from;
        }
        if (isset($to)) {
            $params['to'] = $to;
        }

        $params['output'] = $output;
        $suffix = http_build_query($params);
        $url .= "?$suffix";

        $this->debug('FIXING INTERFACE NAMES', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'params' => $params,
            'suffix' => $suffix,
            'url' => $url,
        ]);

        $this->result = $this->curl->get($url);

        return (!isset($this->result['image'])) ? null : $this->result['image'];
    }

    /**
     * Get a graph of a port for a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null array['ifName']['type'=>'image/png','src'=>'raw image']
     *
     * @see https://docs.librenms.org/API/Devices/#get_graph_by_port_hostname
     */
    public function getPort(int|string $hostname, array $interfaces = null, string $type = 'port_bits'): ?array
    {
        $result = [];
        $device = $this->getDeviceOrException($hostname);
        $ports = $this->fixInterfaceArray($hostname, $interfaces);

        foreach ($ports as $port) {
            $url = $this->curl->getApiUrl("/devices/$device->device_id/ports/".urlencode($port->ifName)."/$type");
            $this->result = $this->curl->get($url);

            if (!is_array($this->result)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }
            $result[$port->ifName] = $this->result['image'];
        }

        return (0 === count($result)) ? null : $result;
    }

    private function fixInterfaceArray(int|string $hostname, array|null $interfaces)
    {
        $ports = $this->getDeviceIfNames($hostname);
        $interfaces = (isset($interfaces)) ? $interfaces : $ports;
        $interfaces = (!is_array($interfaces)) ? [] : $interfaces;

        $this->debug('FIXING INTERFACE NAMES', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'hostname' => $hostname,
            'ports' => $ports,
            'interfaces' => $interfaces,
        ]);

        $result = [];
        foreach ($interfaces as $infname) {
            // @codeCoverageIgnoreStart
            if (!in_array($infname, $ports)) {
                continue;
            }
            // @codeCoverageIgnoreEnd
            $result[] = (object) ['ifName' => $infname];
        }

        $this->debug('FIXING INTERFACE NAMES', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'hostname' => $hostname,
            'result' => $result,
        ]);

        return $result;
    }
}
