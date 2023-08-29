<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
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
 * @covers \LibrenmsApiClient\Cache
 * @covers \LibrenmsApiClient\Sensor
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Common
 */
class SensorTest extends TestCase
{
    private Sensor $sensor;
    private int $router_id;

    public function testGetListing()
    {
        $obj = $this->sensor;
        $result = $obj->getListing();
        $this->assertIsArray($result);
    }

    public function testGet()
    {
        $obj = $this->sensor;

        $device = $obj->getDevice($this->router_id);
        $this->assertIsObject($device);

        $result = $obj->get($device->device_id);
        $this->assertIsArray($result);
        $result = $obj->get('NO SUCH DEVICE');
        $this->assertNull($result);
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
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->sensor = $api->get(Sensor::class);
            $this->router_id = $settings['router_id'];
        }
    }
}
