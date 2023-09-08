<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Location;

/**
 * Location API Unit tests.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Cache
 * @covers \LibrenmsApiClient\Common
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\FileLogger
 * @covers \LibrenmsApiClient\Location
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\PortCache
 */
class LocationTest extends BaseTest
{
    private Location $location;

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

    public function testAddException()
    {
        $obj = $this->location;
        $result = $obj->getListing();
        $this->assertIsArray($result);
        $location = array_pop($result);
        $result = $obj->get($location->location);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_LOCATION_EXIST);
        $obj->add($result->location, $result->lat, $result->lng);
    }

    public function testAdd()
    {
        $obj = $this->location;
        $result = $obj->add('TEST LOCATION', '37.4220041', '-122.0862462');
        $this->assertTrue($result);

        $result = $obj->edit('TEST LOCATION', 'TEST LOCATION1', '37.4220041', '-122.0862462', false);
        $this->assertTrue($result);

        $result = $obj->delete('TEST LOCATION1');
        $this->assertTrue($result);
    }

    public function setUp(): void
    {
        if (!isset($this->location)) {
            $this->location = $this->api->get(Location::class);
        }
    }
}
