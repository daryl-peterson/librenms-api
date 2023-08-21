<?php

namespace LibrenmsApiClient;

use stdClass;

/**
 * LibreNMS API graphs.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Graph
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
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
        $url = $this->api->getApiUrl("/devices/$hostname/graphs");
        $response = $this->api->get($url);

        if (!isset($response)) {
            return null;
        }

        if (0 === count($response['graphs'])) {
            return null;
        }

        return $response['graphs'];
    }

    /**
     * Get a specific graph for a device, this does not include ports.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
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
        $url = $this->api->getApiUrl("/devices/$hostname/$type");
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
        $response = $this->api->get($url);

        if (!isset($response['image'])) {
            return null;
        }

        return $response['image'];
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
        $device = $this->api->device->get($hostname);
        if (!isset($device) || !isset($device->hostname)) {
            return null;
        }

        if (isset($interfaces)) {
            $ports = $this->fixInterfaceArray($interfaces);
        } else {
            $ports = $this->api->port->getByDevice($device->device_id);
        }

        if (!isset($ports)) {
            return null;
        }

        foreach ($ports as $port) {
            try {
                $url = $this->api->getApiUrl("/devices/$hostname/ports/".urlencode($port->ifName)."/$type");
                $response = $this->api->get($url);

                if (!is_array($response)) {
                    continue;
                }
                $result[$port->ifName] = $response['image'];
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
