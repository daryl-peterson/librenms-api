<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Vlan;

/**
 * LibreNMS API Vlan unit test.
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
 * @covers \LibrenmsApiClient\Vlan
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\PortCache
 */
class VlanTest extends BaseTest
{
    private Vlan $vlan;

    public function test()
    {
        $obj = $this->vlan;
        $result = $obj->getListing();
        $this->assertIsArray($result);

        $vlan = array_pop($result);
        $result = $obj->get($this->switchId);
        $this->assertIsArray($result);

        // $this->expectException(ApiException::class);
        $result = $obj->get(999999);
        $this->assertNull($result);
    }

    public function setUp(): void
    {
        if (!isset($this->vlan)) {
            $this->vlan = $this->api->get(Vlan::class);
        }
    }
}
