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
class Graph
{
    private ApiClient $api;
    private Curl $curl;
    private Device $device;
    private Port $port;

    public array|null $result;

    public function __construct(Curl $curl, Device $device, Port $port)
    {
        $this->curl = $curl;
        $this->device = $device;
        $this->port = $port;
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
        $device = $this->device->get($hostname);
        if (!isset($device) || !isset($device->hostname)) {
            return null;
        }

        $hostname = $device->device_id;

        $url = $this->curl->getApiUrl("/devices/$hostname/$type");
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
            return null;
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
        $device = $this->device->get($hostname);
        if (!isset($device) || !isset($device->hostname)) {
            return null;
        }

        if (isset($interfaces)) {
            $ports = $this->fixInterfaceArray($interfaces);
        } else {
            $ports = $this->port->getByDevice($device->device_id);
        }

        if (!isset($ports)) {
            return null;
        }

        foreach ($ports as $port) {
            try {
                $url = $this->curl->getApiUrl("/devices/$hostname/ports/".urlencode($port->ifName)."/$type");
                $this->result = $this->curl->get($url);

                if (!is_array($this->result)) {
                    continue;
                }
                $result[$port->ifName] = $this->result['image'];
            } catch (\Throwable $th) {
                $message = $th->getMessage();
                if (str_contains($message, 'No Data ')) {
                    continue;
                } else {
                    throw new \Exception($message);
                }
            }
        }

        if (0 === count($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Write image to file.
     */
    public function writeToFile(array $image, string $dest): bool
    {
        try {
            $result = file_put_contents($dest, $image['src']);
        } catch (\Throwable $th) {
            return false;
        }

        if (!$result) {
            return false;
        }

        return true;
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
