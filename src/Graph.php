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
    protected Curl $curl;
    public array|null $result;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

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

        if (!isset($this->result['graphs']) || (0 === count($this->result['graphs']))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['graphs'];
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
     */
    public function getByType(
        int|string $hostname,
        string $type,
        string $from = null,
        string $to = null,
        string $output = null
    ): ?array {
        $device = $this->getDevice($hostname);
        if (!isset($device)) {
            return null;
        }

        $url = $this->curl->getApiUrl("/devices/$device->device_id/$type");
        $params = [];
        if (isset($from)) {
            $params['from'] = $from;
        }
        if (isset($to)) {
            $params['to'] = $to;
        }
        if (isset($output)) {
            $params['output'] = $output;
        }

        $suffix = http_build_query($params);
        $url .= "?$suffix";
        $this->result = $this->curl->get($url);

        if (!isset($this->result['image'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['image'];
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
    public function getPort(int|string $hostname, array $interfaces = null, string $type = null): ?array
    {
        $result = [];
        if (!isset($type)) {
            $type = 'port_bits';
        }
        $list = $this->getDevicePorts($hostname);
        if (!isset($list)) {
            return null;
        }

        $device = $this->getDevice($hostname);
        if (!isset($device) || !isset($device->hostname)) {
            return null;
        }

        if (isset($interfaces)) {
            $ports = $this->fixInterfaceArray($interfaces);
        } else {
            $ports = $list;
        }

        if (!isset($ports)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        foreach ($ports as $port) {
            try {
                $url = $this->curl->getApiUrl("/devices/$device->device_id/ports/".urlencode($port->ifName)."/$type");
                $this->result = $this->curl->get($url);

                if (!is_array($this->result)) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }
                $result[$port->ifName] = $this->result['image'];
            } catch (\Throwable $th) {
                $message = $th->getMessage();

                if (str_contains($message, 'No Data ')) {
                    continue;
                }
                if (str_contains($message, 'No Authorization')) {
                    return null;
                }
                // @codeCoverageIgnoreStart
                throw new \Exception($message);
                // @codeCoverageIgnoreEnd
            }
        }

        if (0 === count($result)) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $result;
    }

    public function getCurl()
    {
        return $this->curl;
    }

    private function fixInterfaceArray(array $interfaces)
    {
        $result = [];
        foreach ($interfaces as $infname) {
            $result[] = (object) ['ifName' => $infname];
        }

        return $result;
    }
}
