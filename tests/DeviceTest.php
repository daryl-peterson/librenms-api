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
    private int $router_id;
    private int $switch_id;
    private string $hostname;
    private string $hostname_new;

    public function testGet()
    {
        $obj = $this->device;
        $result = $obj->get($this->router_id);
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
        $result = $obj->getPorts($this->router_id);
        $this->assertIsArray($result);
    }

    public function testDiscover()
    {
        $result = $this->device->discover($this->router_id);
        $this->assertTrue($result);

        $result = $this->device->discover('BLAH BLAH');
        $this->assertFalse($result);
    }

    public function testGetIp()
    {
        $obj = $this->device;
        $result = $obj->getIpList($this->router_id);
        $this->assertIsArray($result);

        $result = $obj->getIpList(0);
        $this->assertNull($result);
    }

    public function testAvailability()
    {
        $obj = $this->device;
        $result = $obj->getAvailability($this->router_id);
        $this->assertIsArray($result);

        $result = $obj->getAvailability(0);
        $this->assertNull($result);
    }

    public function testGetOutages()
    {
        $obj = $this->device;
        $result = $obj->getOutages($this->router_id);
        $this->assertIsArray($result);

        $result = $obj->getOutages(0);
        $this->assertNull($result);
    }

    public function testGetFdb()
    {
        $obj = $this->device;

        $result = $obj->getFbd(0);
        $this->assertNull($result);

        $result = $obj->getFbd($this->router_id);
        $this->assertNull($result);

        $result = $obj->getFbd($this->switch_id);
        $this->assertIsArray($result);
    }

    public function testAddException()
    {
        $obj = $this->device;

        $result = $obj->add([]);
        $this->assertNull($result);

        $device = [
            'hostname' => 'blah',
            'snmpver' => 'v2',
        ];

        $this->expectException(ApiException::class);
        $result = $obj->add($device);
    }

    public function testDeviceActions()
    {
        $obj = $this->device;
        $device = $obj->getDevice($this->hostname);

        if (isset($device)) {
            $result = $obj->delete($this->hostname);
            $this->assertIsArray($result);
            sleep(60);
        }

        $def = [
            'hostname' => $this->hostname,
            'snmpver' => 'v2c',
            'community' => 'blah',
            'sysName' => 'tester',
            'hardware' => 'tester',
            'snmp_disable' => true,
            'force_add' => true,
        ];
        $result = $obj->add($def);
        $this->assertIsObject($result);

        $result = $obj->rename(0, $this->hostname_new);
        $this->assertFalse($result);

        $result = $obj->maintenance(0, '02:00');
        $this->assertFalse($result);

        $result = $obj->delete(0);
        $this->assertNull($result);

        $result = $obj->update(0, 'blah', 1);
        $this->assertFalse($result);

        $result = $obj->update($this->hostname, 'blah', 1);
        $this->assertFalse($result);

        $result = $obj->rename(0, $this->hostname_new);
        $this->assertFalse($result);

        $device = $obj->getDevice($this->hostname);
        if (!isset($device)) {
            return;
        }

        sleep(300);
        $result = $obj->rename($this->hostname, $this->hostname_new);
        $this->assertTrue($result);

        $result = $obj->update($this->hostname_new, 'ignore', 1);
        $result = $obj->update($this->hostname_new, 'disabled', 1);
        $this->assertTrue($result);

        $start = date('Y-m-d 00:00:00');
        $result = $obj->maintenance($this->hostname_new, '23:00', 'Test Maintenance', 'blah', $start);
        $this->assertTrue($result);

        $device = $obj->getDevice($this->hostname);
        if (isset($device)) {
            $result = $obj->delete($this->hostname);
            $this->assertIsArray($result);
        }

        $device = $obj->getDevice($this->hostname_new);
        if (isset($device)) {
            $result = $obj->delete($this->hostname_new);
            $this->assertIsArray($result);
        }
    }

    public function setUp(): void
    {
        if (!isset($this->device)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->device = $api->get(Device::class);
            $this->router_id = $settings['router_id'];
            $this->switch_id = $settings['switch_id'];
            $this->hostname = $settings['test_add_ip'];
            $this->hostname_new = $settings['test_add_gw'];
        }
    }
}
