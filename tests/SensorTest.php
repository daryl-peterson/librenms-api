<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Device;
use LibrenmsApiClient\Sensor;
use PHPUnit\Framework\TestCase;

/**
 * Class description.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Sensor
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Device
 */
class SensorTest extends TestCase
{
    private Sensor $sensor;
    private Device $device;

    public function testGetListing()
    {
        $obj = $this->sensor;
        $result = $obj->getListing();
        $this->assertIsArray($result);
    }

    public function testGet()
    {
        $obj = $this->sensor;

        $devices = $this->device->getListing();
        $this->assertIsArray($devices);
        $device = array_pop($devices);

        $result = $obj->get($device->device_id);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $obj->get('NO SUCH DEVICE');
    }

    public function testGetByClass()
    {
        $obj = $this->sensor;
        $result = $obj->getByClass('temperature');
        $this->assertIsArray($result);

        $result = $obj->getByClass('blah');
        $this->assertNull($result);
    }

    public function setUp(): void
    {
        if (!isset($this->sensor)) {
            global $url,$token;

            $api = new ApiClient($url, $token);
            $this->device = $api->container->get(Device::class);
            $this->sensor = $api->container->get(Sensor::class);
        }
    }
}
