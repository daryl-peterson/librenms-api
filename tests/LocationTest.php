<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Location;
use PHPUnit\Framework\TestCase;

/**
 * Location API Unit tests.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\Arp
 * @covers \LibrenmsApiClient\Alert
 * @covers \LibrenmsApiClient\AlertRule
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Component
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Device
 * @covers \LibrenmsApiClient\DeviceGroup
 * @covers \LibrenmsApiClient\Graph
 * @covers \LibrenmsApiClient\Health
 * @covers \LibrenmsApiClient\Inventory
 * @covers \LibrenmsApiClient\Link
 * @covers \LibrenmsApiClient\Location
 * @covers \LibrenmsApiClient\Log
 * @covers \LibrenmsApiClient\Port
 * @covers \LibrenmsApiClient\Sensor
 * @covers \LibrenmsApiClient\System
 * @covers \LibrenmsApiClient\Vlan
 * @covers \LibrenmsApiClient\Wireless
 */
class LocationTest extends TestCase
{
    private Location $location;
    private $name;

    public function testAdd()
    {
        $obj = $this->location;
        $result = $obj->add($this->name, '37.4220041', '-122.0862462');
        $this->assertTrue($result);

        $this->expectException(ApiException::class);
        $result = $obj->add($this->name, '37.4220041', '-122.0862462');
    }

    public function testEdit()
    {
        $obj = $this->location;

        $result = $obj->edit($this->name, $this->name.'1', '31.909883', '-98.619538', true);
        $this->assertTrue($result);

        $result = $obj->delete($this->name.'1');
        $this->assertTrue($result);

        $this->expectException(ApiException::class);
        $obj->delete($this->name.'999');

        $this->expectException(ApiException::class);
        $obj->edit($this->name.'2');
    }

    public function testGetListing()
    {
        $result = $this->location->getListing();
        $this->assertIsArray($result);
    }

    public function testGet()
    {
        $obj = $this->location;
        $result = $obj->getListing();
        $this->assertIsArray($result);
        $location = array_pop($result);

        $result = $obj->get($location->location);
        $this->assertIsObject($result);
        $this->assertEquals($location->location, $result->location);

        $this->expectException(ApiException::class);
        $result = $obj->get(0);
    }

    public function setUp(): void
    {
        $this->name = 'TEST LOCATION';
        if (!isset($this->location)) {
            global $url,$token;

            $api = new ApiClient($url, $token);
            $this->location = $api->container->get(Location::class);
        }
    }
}
