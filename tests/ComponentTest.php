<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\Component;

/**
 * LibreNMS API Component testing.
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
 * @covers \LibrenmsApiClient\Component
 * @covers \LibrenmsApiClient\DeviceCache
 */
class ComponentTest extends BaseTest
{
    private Component $component;

    public function testAddGetEditDelete()
    {
        $comp = $this->component;

        $device = $comp->getDevice($this->routerId);
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
        if (!isset($this->component)) {
            $this->component = $this->api->get(Component::class);
        }
    }
}
