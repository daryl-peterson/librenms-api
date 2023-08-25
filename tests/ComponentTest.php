<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use PHPUnit\Framework\TestCase;

/**
 * LibreNMS API Component testing.
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
class ComponentTest extends TestCase
{
    private ApiClient $api;

    public function testAddGetEditDelete()
    {
        $comp = $this->api->component;

        $devices = $this->api->device->getListing();
        $device = array_pop($devices);
        $this->assertIsObject($device);

        $resultOrg = $comp->add($device->device_id, 'API TEST');
        $this->assertIsObject($resultOrg);
        $id = $resultOrg->id;

        $result = $comp->get($device->device_id, $resultOrg->id);
        $this->assertIsObject($result);

        $result1 = $comp->get($device->device_id, $resultOrg->id, $resultOrg->type);
        $this->assertIsObject($result1);

        $label = 'This is a test label';
        $status = $ignore = $disable = 1;

        $result = $comp->edit($device->device_id, $id, ['label' => $label, 'status' => $status, 'ignore' => $ignore, 'disable' => $disable]);
        $this->assertTrue($result);

        $result2 = $comp->get($device->device_id, null, null, $label);
        $this->assertIsObject($result2);

        $result = $comp->get($device->device_id, null, null, null, $status, $disable, $ignore);
        $this->assertIsObject($result);

        $result = $comp->delete($device->device_id, $id);
        $this->assertTrue($result);
    }

    public function setUp(): void
    {
        if (!isset($this->api)) {
            global $url,$token;

            $this->api = new ApiClient($url, $token);
        }
    }
}
