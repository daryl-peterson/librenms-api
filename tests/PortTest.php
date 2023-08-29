<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\Port;
use PHPUnit\Framework\TestCase;

/**
 * LibreNMS API Port unit test.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Port
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Common
 */
class PortTest extends TestCase
{
    private Port $port;
    private int $router_id;
    private string $router_if;

    public function testGetListing()
    {
        $obj = $this->port;
        $result = $obj->getListing();
        $this->assertIsArray($result);

        $result = $obj->getStats($this->router_id, $this->router_if);
        $this->assertIsObject($result);

        $port_id = $result->port_id;

        $result = $obj->get($port_id);
        $this->assertIsObject($result);

        $result = $obj->getIpInfo($port_id);
        $this->assertIsArray($result);
    }

    public function testGetStats()
    {
        $obj = $this->port;

        $result = $obj->getStats(0, $this->router_if);
        $this->assertNull($result);

        $result = $obj->getStats($this->router_id, $this->router_if);
        $this->assertIsObject($result);
    }

    public function test()
    {
        $obj = $this->port;
        $ports = $obj->getByDevice($this->router_id);
        $this->assertIsArray($ports);
    }

    public function setUp(): void
    {
        if (!isset($this->port)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);

            $this->port = $api->get(Port::class);
            $this->router_id = $settings['router_id'];
            $this->router_if = $settings['router_if'];
        }
    }
}
