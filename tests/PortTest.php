<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Cache;
use LibrenmsApiClient\Port;
use LibrenmsApiClient\PortCache;
use PHPUnit\Framework\TestCase;

/**
 * LibreNMS API Port unit test.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Common
 * @covers \LibrenmsApiClient\Cache
 * @covers \LibrenmsApiClient\FileLogger
 * @covers \LibrenmsApiClient\Port
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\PortCache
 * @covers \LibrenmsApiClient\IfNamesCache
 */
class PortTest extends TestCase
{
    private Port $port;
    private Cache $cache;
    private int $routerId;
    private string $ifName;

    public function testGet()
    {
        $obj = $this->port;

        $result = $obj->get(0);
        $this->assertNull($result);

        $ports = $obj->getDevicePorts($this->routerId);
        foreach ($ports as $port) {
            if ($port->ifName !== $this->ifName) {
                continue;
            }
            $portId = $port->port_id;
        }

        if (isset($portId)) {
            $result = $obj->get($portId);
            $this->assertIsObject($result);
        }
    }

    public function testGetListing()
    {
        $obj = $this->port;
        $result = $obj->getListing();
        $this->assertIsArray($result);
    }

    public function testGetIpInfo()
    {
        $obj = $this->port;

        $result = $obj->getIpInfo(0);
        $this->assertNull($result);

        $ports = $obj->getDevicePorts($this->routerId);
        foreach ($ports as $port) {
            if ($port->ifName !== $this->ifName) {
                continue;
            }
            $portId = $port->port_id;
        }

        if (isset($portId)) {
            $result = $obj->getIpInfo($portId);
            $this->assertIsArray($result);
        }
    }

    public function testGetIfNames()
    {
        $obj = $this->port;
        $ifNames = $obj->getDeviceIfNames($this->routerId);
        $this->assertIsArray($ifNames);
    }

    public function testSearch()
    {
        $obj = $this->port;

        $ports = $obj->getDevicePorts($this->routerId);
        foreach ($ports as $port) {
            if ($port->ifName !== $this->ifName) {
                continue;
            }
            $alias = $port->ifAlias;
        }

        if (isset($alias)) {
            $result = $obj->search($alias, 'ifAlias');
            $this->assertIsArray($result);

            $result = $obj->search($alias);
            $this->assertIsArray($result);

            $result = $obj->searchBy($alias, 'ifAlias', $obj->portColumns);
            $this->assertIsArray($result);

            $result = $obj->searchBy($alias);
            $this->assertIsArray($result);
        }
    }

    public function testGetByDevice()
    {
        $obj = $this->port;
        $pc = $this->cache->pool;
        $pc->delete(Cache::PORT_KEY.$this->routerId);

        $ports = $obj->getDevicePorts($this->routerId);
        $this->assertIsArray($ports);

        $ports = $obj->getDevicePorts($this->routerId, $obj->portColumns);
        $this->assertIsArray($ports);
    }

    public function testGetByMac()
    {
        $obj = $this->port;

        $ports = PortCache::get($this->routerId);
        foreach ($ports as $port) {
            if ($port->ifName !== $this->ifName) {
                continue;
            }
            $mac = $port->ifPhysAddress;
        }

        if (isset($mac)) {
            $result = $obj->getByMac($mac, 'first');
            $this->assertIsObject($result);
        }
    }

    public function testGetStats()
    {
        $obj = $this->port;

        $result = $obj->getStats($this->routerId, $this->ifName);
        $this->assertIsObject($result);

        $result = $obj->getStats($this->routerId, 'blah');
        $this->assertNull($result);

        $this->expectException(ApiException::class);
        $obj->getStats(0, 'blah');
    }

    public function setUp(): void
    {
        if (!isset($this->port)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);

            $this->port = $api->get(Port::class);
            $this->cache = Cache::getInstance();
            $this->routerId = $settings['router_id'];
            $this->ifName = $settings['router_if'];
        }
    }
}
