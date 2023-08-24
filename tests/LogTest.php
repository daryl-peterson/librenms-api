<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\Log;
use PHPUnit\Framework\TestCase;

/**
 * Log API Unit tests.
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
class LogTest extends TestCase
{
    private ApiClient $api;
    private Log $log;

    public function testGetAlerts()
    {
        $log = $this->api->log;
        $result = $log->getAlertLogs(null, 1);
        $this->assertIsArray($result);
    }

    public function testGetAlertsHostName()
    {
        $log = $this->api->log;
        $result = $log->getAlertLogs(null, 1);
        if (!isset($result)) {
            return;
        }
        $alert = array_pop($result['logs']);
        $result = $log->getAlertLogs($alert->device_id, 1);
        $this->assertIsArray($result);

        $result = $log->getAlertLogs($alert->device_id, 1, 0);
        $this->assertIsArray($result);

        $result = $log->getAlertLogs(null, 1, 0, $alert->time_logged);
        $this->assertIsArray($result);

        $to = strtotime($alert->time_logged.'60 minute');
        $result = $log->getAlertLogs(null, 1, 0, $alert->time_logged, $to);
        $this->assertIsArray($result);
    }

    public function testGetEvents()
    {
        $log = $this->api->log;
        $result = $log->getEventLogs(null, 1);
        $this->assertIsArray($result);
    }

    public function testGetAuths()
    {
        $log = $this->api->log;
        $result = $log->getAuthLogs(null, 1);
        $this->assertIsArray($result);
    }

    public function testGetSysLogs()
    {
        $log = $this->api->log;
        $result = $log->getSysLogs(null);

        if (is_array($result) || !isset($result)) {
            $result = true;
        } else {
            $result = false;
        }
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
