<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Graph;

/**
 * LibreNMS API Graph unit test.
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
 * @covers \LibrenmsApiClient\Graph
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\PortCache
 * @covers \LibrenmsApiClient\IfNamesCache
 */
class GraphTest extends BaseTest
{
    private Graph $graph;

    public function testGetPort()
    {
        $graph = $this->graph;

        $device = $graph->getDevice($this->routerId);
        $this->assertIsObject($device);

        $result = $graph->getPort($this->routerId);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $graph->getPort(0);

        $result = $graph->getPort($this->routerId, ['blah', 'nope']);
        $this->assertNull($result);
    }

    public function testGetByType()
    {
        $graph = $this->graph;

        $result = $graph->getTypes($this->routerId);
        $this->assertIsArray($result);
        $type = array_pop($result);

        $result = $graph->getByType($this->routerId, $type->name);
        $this->assertIsArray($result);

        $from = date('m/d/Y 00:00');
        $to = date('m/d/Y 12:00');
        $result = $graph->getByType($this->routerId, $type->name, $from, $to, 'display');
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage(ApiException::ERR_DEVICE_NOT_EXIST);
        $result = $graph->getByType(0, $type->name);
    }

    public function setUp(): void
    {
        if (!isset($this->graph)) {
            $this->graph = $this->api->get(Graph::class);
        }
    }
}
