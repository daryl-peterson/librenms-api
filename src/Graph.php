<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API graphs.
 *
 * @category
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

    public function types(int|string $hostname)
    {
        // /devices/:hostname/graphs
    }

    /**
     * Get a graph of a port for a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#get_graph_by_port_hostname
     */
    public function device(int|string $hostname, array $interfaces = null, string $type = null): ?array
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
                $response = $this->api->get($url, false);

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

    private function fixInterfaceArray(array $interfaces)
    {
        $result = [];
        foreach ($interfaces as $infname) {
            $result[] = (object) ['ifName' => $infname];
        }

        return $result;
    }
}
