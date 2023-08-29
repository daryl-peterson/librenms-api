<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Device;
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
 * @covers \LibrenmsApiClient\Device
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Common
 */
class DeviceTest extends TestCase
{
    private Device $device;
    private int $deviceId;

    public function testGet()
    {
        $obj = $this->device;
        $result = $obj->get($this->deviceId);
        $this->assertIsObject($result);

        $result = $obj->getByIpV4($result->ip);
        $this->assertIsArray($result);
    }

    public function testGetListing()
    {
        $obj = $this->device;
        $result = $obj->getListing();
        $this->assertIsArray($result);
    }

    public function testGetPorts()
    {
        $obj = $this->device;
        $result = $obj->getPorts($this->deviceId);
        $this->assertIsArray($result);
    }

    public function testDiscover()
    {
        $result = $this->device->discover($this->deviceId);
        $this->assertTrue($result);

        $result = $this->device->discover('BLAH BLAH');
        $this->assertFalse($result);
    }

    public function testGetIp()
    {
        $obj = $this->device;
        $result = $obj->getIpList($this->deviceId);
        $this->assertIsArray($result);

        $result = $obj->getIpList(0);
        $this->assertNull($result);
    }

    public function testAvailability()
    {
        $obj = $this->device;
        $result = $obj->getAvailability($this->deviceId);
        $this->assertIsArray($result);

        $result = $obj->getAvailability(0);
        $this->assertNull($result);
    }

    public function testGetOutages()
    {
        $obj = $this->device;
        $result = $obj->getOutages($this->deviceId);
        $this->assertIsArray($result);

        $result = $obj->getOutages(0);
        $this->assertNull($result);
    }

    public function testHasSNMP()
    {
        $obj = $this->device;
        $result = $obj->hasSNMP($this->deviceId);
        $this->assertTrue($result);

        $result = $obj->hasSNMP(0);
        $this->assertFalse($result);
    }

    public function testAdd()
    {
        $obj = $this->device;
        $hostname = '192.168.1.1';

        $device = [
            'hostname' => $hostname,
            'snmpver' => 'v2',
        ];

        $this->expectException(ApiException::class);
        $result = $obj->add($device);

        $device = [
            'hostname' => $hostname,
            'snmpver' => 'v2c',
            'community' => 'blah',
            'sysName' => 'tester',
            'hardware' => 'tester',
            'snmp_disable' => true,
            'force_add' => true,
        ];
        $result = $obj->add($device);
        $this->assertIsArray($result);

        $result = $obj->delete($hostname);
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->device)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->device = $api->get(Device::class);
            $this->deviceId = $settings['device_id'];
        }
    }
}
