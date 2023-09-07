<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Sensor;
use LibrenmsApiClient\SensorCache;
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
 * @covers \LibrenmsApiClient\Cache
 * @covers \LibrenmsApiClient\Sensor
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Common
 * @covers \LibrenmsApiClient\FileLogger
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\PortCache
 * @covers \LibrenmsApiClient\SensorCache
 */
class SensorTest extends TestCase
{
    private Sensor $sensor;
    private int $routerId;

    /**
     * This must be run first. If sensor is not it cache it calls getListing.
     *
     * @return void
     */
    public function testGet()
    {
        $obj = $this->sensor;

        SensorCache::delete($this->routerId);
        $result = $obj->get($this->routerId);
        $this->assertIsArray($result);

        $result = $obj->get($this->routerId);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $result = $obj->get(0);
    }

    public function testGetListing()
    {
        $obj = $this->sensor;
        $result = $obj->getListing();
        $this->assertIsArray($result);
    }

    public function testGetByClass()
    {
        $obj = $this->sensor;
        $result = $obj->getByClass('temperature');
        $this->assertIsArray($result);

        $result = $obj->getByClass('blah');
        $this->assertNull($result);
    }

    public function testSensorCache()
    {
        $result = SensorCache::set(null);
        $this->assertNull($result);
    }

    public function setUp(): void
    {
        if (!isset($this->sensor)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->sensor = $api->get(Sensor::class);
            $this->routerId = $settings['router_id'];
        }
    }
}
