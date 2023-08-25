<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Link;
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
class LinkTest extends TestCase
{
    private ApiClient $api;
    private Link $link;

    public function testGetById()
    {
        $link = $this->link;
        $result = $link->getListing();
        $object = array_pop($result);

        $result = $link->getById((int) $object->id);
        $this->assertIsObject($result);

        $this->expectException(ApiException::class);
        $result = $link->getById(0);
    }

    public function testGetByHost()
    {
        $link = $this->link;
        $result = $link->getListing();
        $object = array_pop($result);

        $result = $link->getByHost($object->local_device_id);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $result = $link->getByHost(0);
    }

    public function testGetListing()
    {
        $link = $this->link;
        $result = $link->getListing();
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->api)) {
            global $url,$token;

            $this->api = new ApiClient($url, $token);
            $this->link = $this->api->link;
        }
    }
}
