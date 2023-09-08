<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Device;
use LibrenmsApiClient\DeviceValidator;

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
 * @covers \LibrenmsApiClient\Common
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\FileLogger
 * @covers \LibrenmsApiClient\Device
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\DeviceValidator
 * @covers \LibrenmsApiClient\PortCache
 * @covers \LibrenmsApiClient\IfNamesCache
 */
class DeviceTest extends BaseTest
{
    private Device $device;
    private DeviceValidator $validator;

    private \stdClass $router;

    private string $hostname;
    private string $hostname_new;

    public function testGet()
    {
        $obj = $this->device;
        $result = $obj->get($this->routerId);
        $this->assertIsObject($result);
    }

    public function testGetIfNames()
    {
        $obj = $this->device;

        $result = $obj->getDeviceIfNames($this->routerId);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_DEVICE_NOT_EXIST);
        $obj->getDeviceIfNames(0);
    }

    public function testGetListing()
    {
        $obj = $this->device;
        $result = $obj->getListing();
        $this->assertIsArray($result);
    }

    public function testDiscover()
    {
        $result = $this->device->discover($this->routerId);
        $this->assertTrue($result);

        $this->expectException(ApiException::class);
        $this->device->discover('BLAH BLAH');
    }

    public function testGetIp()
    {
        $obj = $this->device;
        $result = $obj->getIpList($this->routerId);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $obj->getIpList(0);
    }

    public function testAvailability()
    {
        $obj = $this->device;
        $result = $obj->getAvailability($this->routerId);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $obj->getAvailability(0);
    }

    public function testGetOutages()
    {
        $obj = $this->device;
        $result = $obj->getOutages($this->routerId);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $obj->getOutages(0);
    }

    public function testGetFdbException()
    {
        $obj = $this->device;

        $this->expectException(ApiException::class);
        $obj->getFbd(0);
    }

    public function testGetFdb()
    {
        $obj = $this->device;

        $result = $obj->getFbd($this->routerId);
        $this->assertNull($result);

        $result = $obj->getFbd($this->switchId);
        $this->assertIsArray($result);
    }

    public function testAddExceptionMissingHostname()
    {
        $obj = $this->device;
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_HOSTNAME_IP);
        $obj->add([]);
    }

    public function testAddExceptionDeviceExist()
    {
        $obj = $this->device;
        $result = $obj->get($this->routerId);
        $this->assertIsObject($result);

        $def = [
            'hostname' => $result->hostname,
            'snmpver' => 'v2c',
        ];

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_DEVICE_DOES_EXIST);
        $obj->add($def);
    }

    public function testAddExceptionInvalidSNMP()
    {
        $obj = $this->device;

        $device = [
            'hostname' => 'blah',
            'snmpver' => 'v2',
        ];

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_INVALID_SNMP);
        $obj->add($device);
    }

    public function testAddMock()
    {
        /**
         * @var MockObject&MockedType
         */
        $mock = $this->getMockBase(['doAdd']);

        $def = [
            'hostname' => $this->hostname,
            'snmpver' => 'v2c',
            'community' => 'blah',
            'sysName' => 'tester',
            'hardware' => 'tester',
            'snmp_disable' => true,
            'force_add' => true,
        ];

        $mock->expects($this->once())
            ->method('doAdd')
            ->willReturn(null);

        $mock->add($def);
    }

    public function testRenameExceptionDeviceNotExist()
    {
        $obj = $this->device;
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_DEVICE_NOT_EXIST);
        $obj->rename('blah123', 'blah1234');
    }

    public function testRenameExceptionDeviceExist()
    {
        $obj = $this->device;
        $result = $obj->get($this->routerId);
        $this->assertIsObject($result);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_DEVICE_DOES_EXIST);
        $obj->rename($result->hostname, $result->hostname);
    }

    public function testRenameMock()
    {
        $obj = $this->device;
        $result = $obj->get($this->routerId);

        /**
         * @var MockObject&MockedType
         */
        $mock = $this->getMockBase(['doRename']);
        $mock->expects($this->once())
        ->method('doRename')
        ->willReturn(true);

        $mock->rename($result->hostname, 'blah1234');
    }

    public function testUpdateExceptionDeviceNotExist()
    {
        $obj = $this->device;
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_DEVICE_NOT_EXIST);
        $obj->update('blah', 'notes', 'not a real note');
    }

    public function testUpdateExceptionInvalidField()
    {
        $obj = $this->device;
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_INVALID_FIELD);
        $obj->update($this->routerId, 'notes1', 'not a real note');
    }

    public function testUpdateMock()
    {
        /**
         * @var MockObject&MockedType
         */
        $mock = $this->getMockBase(['doUpdate']);
        $mock->expects($this->once())
        ->method('doUpdate')
        ->willReturn(true);

        $mock->update($this->routerId, 'notes', 'this could be a real note');
    }

    public function testDeleteException()
    {
        $obj = $this->device;
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_DEVICE_NOT_EXIST);
        $obj->delete('blah');
    }

    public function testDeleteMock()
    {
        /**
         * @var MockObject&MockedType
         */
        $mock = $this->getMockBase(['doDelete']);
        $mock->expects($this->once())
        ->method('doDelete')
        ->willReturn(true);

        $mock->delete($this->routerId);
    }

    public function testMaintenanceException()
    {
        $obj = $this->device;
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_DEVICE_NOT_EXIST);
        $obj->maintenance('blah', '02:00');
    }

    public function testMaintenanceMock()
    {
        /**
         * @var MockObject&MockedType
         */
        $mock = $this->getMockBase(['doMaintenance']);

        $mock->expects($this->once())
            ->method('doMaintenance')
            ->willReturn(true);

        $start = date('Y-m-d H:i:00');
        $mock->maintenance($this->routerId, '02:00', null, 'Notes', $start);
    }

    private function getMockBase(array $only_methods)
    {
        return $this->getMockBuilder(Device::class)
            ->setConstructorArgs([$this->curl, $this->validator])
            ->onlyMethods($only_methods)
            ->getMock();
    }

    public function setUp(): void
    {
        if (!isset($this->device)) {
            $this->device = $this->api->get(Device::class);
            $this->validator = $this->api->get(DeviceValidator::class);

            $this->hostname = 'SOMEHOSTNAME';
            $this->hostname_new = 'SOMEHOSTNAME2';
        }
    }
}
