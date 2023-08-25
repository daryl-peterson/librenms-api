<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
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
    private ApiClient $api;
    private $name;

    public function testAdd()
    {
        $obj = $this->api->location;

        try {
            $obj->delete($this->name);
            $obj->delete($this->name.'1');
        } catch (\Throwable $th) {
            // throw $th;
        }
        $result = $obj->add($this->name, '37.4220041', '-122.0862462');
        $this->assertTrue($result);

        $this->expectException(ApiException::class);
        $result = $obj->add($this->name, '37.4220041', '-122.0862462');
    }

    public function testEdit()
    {
        $obj = $this->api->location;
        $result = $obj->get($this->name);
        $this->assertIsObject($result);

        $result = $obj->edit($this->name, $this->name.'1', $result->lat, $result->lng, true);
        $this->assertTrue($result);

        $this->expectException(ApiException::class);
        $obj->edit($this->name.'2');
    }

    public function testDelete()
    {
        $obj = $this->api->location;

        $result = $obj->delete($this->name.'1');
        $this->assertTrue($result);

        $this->expectException(ApiException::class);
        $obj->delete($this->name.'999');
    }

    public function testGetListing()
    {
        $result = $this->api->location->getListing();
        $this->assertIsArray($result);
    }

    public function testGet()
    {
        $obj = $this->api->location;
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
        if (!isset($this->api)) {
            global $url,$token;

            $this->api = new ApiClient($url, $token);
        }
    }
}
