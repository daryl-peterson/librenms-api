<?php

declare(strict_types=1);

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\Alert;
use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\Arp;
use LibrenmsApiClient\Component;
use LibrenmsApiClient\Curl;
use LibrenmsApiClient\Device;
use PHPUnit\Framework\TestCase;

/**
 * API Client Unit tests.
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
class ApiClientTest extends TestCase
{
    private ApiClient $api;
    private Curl $curl;

    public function testAlert()
    {
        $obj = $this->api->alert;
        $objTest = new Alert($this->api);
        $this->assertEquals($objTest, $obj);
    }

    public function testArp()
    {
        $obj = $this->api->arp;
        $objTest = new Arp($this->api);
        $this->assertEquals($objTest, $obj);
    }

    public function testComponet()
    {
        $obj = $this->api->component;
        $objTest = new Component($this->api);
        $this->assertEquals($objTest, $obj);
    }

    public function testDevice()
    {
        $obj = $this->api->device;
        $objTest = new Device($this->api);
        $this->assertEquals($objTest, $obj);
    }

    public function setUp(): void
    {
        parent::setUp();

        if (!isset($this->curl)) {
            global $url,$token;

            $this->curl = new Curl($url, $token);
            $this->api = new ApiClient($url, $token);
        }
    }
}
