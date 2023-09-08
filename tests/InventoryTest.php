<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Inventory;

/**
 * Class description.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       1.0.0
 *
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Cache
 * @covers \LibrenmsApiClient\Sensor
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Common
 * @covers \LibrenmsApiClient\FileLogger
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\Inventory
 */
class InventoryTest extends BaseTest
{
    private Inventory $inventory;

    public function testGetListing()
    {
        $obj = $this->inventory;
        $result = $obj->getListing($this->routerId);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $obj->getListing(0);
    }

    public function testGet()
    {
        $obj = $this->inventory;
        $result = $obj->get($this->routerId);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $obj->get(0);
    }

    public function testGetType()
    {
        $obj = $this->inventory;
        $result = $obj->getType();
        $this->assertIsArray($result);
    }

    public function testGetHardWare()
    {
        $obj = $this->inventory;
        $result = $obj->getHardware();
        $this->assertIsArray($result);
    }

    public function testGetVersion()
    {
        $obj = $this->inventory;
        $result = $obj->getVersion();
        $this->assertIsArray($result);
    }

    public function testGetFeature()
    {
        $obj = $this->inventory;
        $result = $obj->getFeature();
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->inventory)) {
            $this->inventory = $this->api->get(Inventory::class);
        }
    }
}
