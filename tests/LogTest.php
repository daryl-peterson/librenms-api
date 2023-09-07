<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
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
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Cache
 * @covers \LibrenmsApiClient\Common
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\FileLogger
 * @covers \LibrenmsApiClient\Log
 * @covers \LibrenmsApiClient\DeviceCache
 */
class LogTest extends TestCase
{
    private Log $log;

    public function testGetAlerts()
    {
        $log = $this->log;
        $result = $log->getAlertLogs(null, 1);
        $this->assertIsArray($result);
    }

    public function testGetAlertLogs1()
    {
        $log = $this->log;
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
        $this->assertIsArray($log->getResult());

        $nt = date('m/d/Y H:i', strtotime('+1 year'));
        $result = $log->getAlertLogs(null, null, null, $nt, $nt);
        $this->assertNull($result);
    }

    public function testGetAlertLogs2()
    {
        $log = $this->log;
        $this->expectException(ApiException::class);
        $log->getAlertLogs('blah', 1, 0);
    }

    public function testGetEventLogs()
    {
        $log = $this->log;
        $result = $log->getEventLogs(null, 1);
        $this->assertIsArray($result);
    }

    public function testGetAuthLogs()
    {
        $log = $this->log;
        $result = $log->getAuthLogs(null, 1);
        $this->assertIsArray($result);
    }

    public function testGetSysLogs()
    {
        $log = $this->log;
        $result = $log->getSysLogs(null);

        if (is_array($result) || !isset($result)) {
            $result = true;
        } else {
            $result = false;
        }
        $this->assertTrue($result);
    }

    public function testSyslogSink()
    {
        $log = $this->log;
        $data = ['msg' => 'API TEST', 'host' => 'mybrain.com'];
        $result = $log->syslogsink($data);
        $this->assertTrue($result);
    }

    public function setUp(): void
    {
        if (!isset($this->log)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->log = $api->get(Log::class);
        }
    }
}
