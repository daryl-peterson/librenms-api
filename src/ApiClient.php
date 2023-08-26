<?php

namespace LibrenmsApiClient;

use DI\Container;
use DI\ContainerBuilder;

/**
 * LibreNMS API Calls.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @version 0.0.2
 */
class ApiClient
{
    public const API_PATH = '/api/v0';

    protected string $url;
    protected string $token;
    public Container $container;

    /**
     * Initialize.
     */
    public function __construct(string $url, string $token)
    {
        $this->url = $url;
        $this->token = $token;

        $curl = new Curl($url, $token);

        $builder = new ContainerBuilder();
        $builder->addDefinitions(
            [
                Curl::class => $curl,

                Alert::class => \DI\autowire(Alert::class)
                    ->constructor(\DI\get(Curl::class)),

                Arp::class => \DI\autowire(Arp::class)
                    ->constructor(\DI\get(Curl::class)),

                Device::class => \DI\autowire(Device::class)
                    ->constructor(\DI\get(Curl::class)),

                Sensor::class => \DI\autowire(Sensor::class)
                    ->constructor(\DI\get(Curl::class), \DI\get(Device::class)),

                System::class => \DI\autowire(System::class)
                    ->constructor(\DI\get(Curl::class)),

                Component::class => \DI\autowire(Component::class)
                    ->constructor(\DI\get(Curl::class)),

                Port::class => \DI\autowire(Port::class)
                    ->constructor(\DI\get(Curl::class)),

                Link::class => \DI\autowire(Link::class)
                    ->constructor(\DI\get(Curl::class)),

                Location::class => \DI\autowire(Location::class)
                    ->constructor(\DI\get(Curl::class)),

                DeviceGroup::class => \DI\autowire(DeviceGroup::class)
                    ->constructor(\DI\get(Curl::class)),

                Vlan::class => \DI\autowire(Vlan::class)
                    ->constructor(\DI\get(Curl::class)),

                Graph::class => \DI\autowire(Graph::class)
                    ->constructor(\DI\get(Curl::class), \DI\get(Device::class), \DI\get(Port::class)),
            ]
        );
        $this->container = $builder->build();
    }

    public function getContainer()
    {
        return $this->container;
    }
}
