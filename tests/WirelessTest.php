<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\Wireless;

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
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Common
 * @covers \LibrenmsApiClient\FileLogger
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\Wireless
 */
class WirelessTest extends BaseTest
{
    private Wireless $wireless;

    public function testHasWireless()
    {
        $obj = $this->wireless;

        $result = $obj->hasWireless($this->routerId);
        $this->assertFalse($result);
    }

    public function setUp(): void
    {
        if (!isset($this->wireless)) {
            $this->wireless = $this->api->get(Wireless::class);
        }
    }
}
