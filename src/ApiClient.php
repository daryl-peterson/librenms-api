<?php

namespace LibrenmsApiClient;

use Cache\Adapter\PHPArray\ArrayCachePool;
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
                ArrayCachePool::class => new ArrayCachePool(),

                Alert::class => \DI\autowire(Alert::class)
                    ->constructor(\DI\get(Curl::class)),

                Arp::class => \DI\autowire(Arp::class)
                    ->constructor(\DI\get(Curl::class)),

                System::class => \DI\autowire(System::class)
                    ->constructor(\DI\get(Curl::class)),

                Component::class => \DI\autowire(Component::class)
                    ->constructor(\DI\get(Curl::class)),

                Inventory::class => \DI\autowire(Inventory::class)
                    ->constructor(\DI\get(Curl::class)),

                Link::class => \DI\autowire(Link::class)
                    ->constructor(\DI\get(Curl::class)),

                Location::class => \DI\autowire(Location::class)
                    ->constructor(\DI\get(Curl::class)),

                DeviceGroup::class => \DI\autowire(DeviceGroup::class)
                    ->constructor(\DI\get(Curl::class)),

                Vlan::class => \DI\autowire(Vlan::class)
                    ->constructor(\DI\get(Curl::class)),

                DeviceValidator::class => \DI\autowire(DeviceValidator::class)
                    ->constructor(),

                Device::class => \DI\autowire(Device::class)
                    ->constructor(\DI\get(Curl::class), \DI\get(DeviceValidator::class)),

                Port::class => \DI\autowire(Port::class)
                    ->constructor(\DI\get(Curl::class)),

                Sensor::class => \DI\autowire(Sensor::class)
                    ->constructor(\DI\get(Curl::class)),

                Graph::class => \DI\autowire(Graph::class)
                    ->constructor(\DI\get(Curl::class)),
            ]
        );
        $this->container = $builder->build();
    }

    /**
     * Get class object.
     */
    public function get(string $class): mixed
    {
        return $this->container->get($class);
    }
}
