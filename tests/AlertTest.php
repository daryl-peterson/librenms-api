<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\Alert;
use LibrenmsApiClient\ApiException;

/**
 * Alert API Unit tests.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\Alert
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Device
 * @covers \LibrenmsApiClient\Log
 */
class AlertTest extends BaseTest
{
    private Alert $alert;

    public function testGet()
    {
        $alert = $this->alert;
        $result = $alert->getListing(0);
        if (!is_array($result)) {
            return;
        }
        $this->assertIsArray($result);

        $record = array_pop($result);
        $result = $alert->get($record->id, $record->state);
        $this->assertIsObject($result);

        $result = $alert->get(1);
        $this->assertNull($result);
    }

    public function testGetListing()
    {
        $alert = $this->alert;
        $result = $alert->getListing(0);
        if (!is_array($result)) {
            return;
        }
        $this->assertIsArray($result);
        $object = array_pop($result);

        $result = $alert->getListing(0, $object->severity);
        $this->assertIsArray($result);

        $result = $alert->getListing(0, $object->severity, 'desc');
        $this->assertIsArray($result);

        $result = $alert->getListing(0, $object->severity, 'desc', $object->rule_id);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $alert->getListing(3, 'blah', -1);
    }

    public function testAll()
    {
        $alert = $this->alert;
        $result = $alert->all();
        $this->assertIsArray($result);
    }

    public function testAcknowledge()
    {
        $alert = $this->alert;
        $result = $alert->getListing(0);
        if (!is_array($result)) {
            return;
        }
        $this->assertIsArray($result);
        $object = array_pop($result);

        $result = $alert->acknowledge($object->id);
        $this->assertTrue($result);

        $result = $alert->acknowledge(-99);
        $this->assertFalse($result);
    }

    public function testUnmute()
    {
        $alert = $this->alert;
        $result = $alert->getListing(0);
        if (!is_array($result)) {
            return;
        }
        $this->assertIsArray($result);
        $object = array_pop($result);

        $alert->acknowledge($object->id);
        $result = $alert->unmute($object->id);
        $this->assertTrue($result);

        $result = $alert->unmute(-1);
        $this->assertFalse($result);
    }

    public function setUp(): void
    {
        if (!isset($this->alert)) {
            $this->alert = $this->api->get(Alert::class);
        }
    }
}
