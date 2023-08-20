<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\Log;
use PHPUnit\Framework\TestCase;

/**
 * Log Unit tests.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class LogTest extends TestCase
{
    private ApiClient $api;
    private Log $log;

    public function testAlert()
    {
        $result = $this->api->log->alert(0);
        $this->assertNull($result);

        $alerts = $this->api->alert->all();

        if (!isset($alerts)) {
            return;
        }

        $alert = array_pop($alerts);
        $result = $this->api->log->alert($alert->device_id);
        $this->assertIsArray($result);
    }

    public function testEvent()
    {
        $log = $this->api->log;
        $result = $log->event(0);
        $this->assertNull($result);

        $listing = $this->api->device->listing();
        print_r($listing);
        if (!isset($listing)) {
            return;
        }
        $device = array_pop($listing);
        $result = $log->event($device->device_id);
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->api)) {
            global $url,$token;

            $this->api = new ApiClient($url, $token);
        }
    }
}
