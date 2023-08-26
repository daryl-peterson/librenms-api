<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\Component;
use LibrenmsApiClient\Curl;
use LibrenmsApiClient\Device;
use PHPUnit\Framework\TestCase;

/**
 * LibreNMS API Component testing.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\Component
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Device
 */
class ComponentTest extends TestCase
{
    private Component $component;
    private Device $device;

    public function testAddGetEditDelete()
    {
        $comp = $this->component;

        $devices = $this->device->getListing();
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
        if (!isset($this->component)) {
            global $url,$token;
            $curl = new Curl($url, $token);
            $this->device = new Device($curl);
            $this->component = new Component($curl);
        }
    }
}
