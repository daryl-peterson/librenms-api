<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Device;
use LibrenmsApiClient\Graph;
use PHPUnit\Framework\TestCase;

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
 * @covers \LibrenmsApiClient\Device
 * @covers \LibrenmsApiClient\Graph
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Port
 */
class GraphTest extends TestCase
{
    private Graph $graph;
    private Device $device;

    public function test()
    {
        $graph = $this->graph;

        $devices = $this->device->getListing();
        $device = array_pop($devices);

        $result = $graph->getPort($device->device_id);
        $this->assertIsArray($result);

        $result = $graph->getTypes($device->device_id);
        $this->assertIsArray($result);
        $type = array_pop($result);

        $result = $graph->getByType($device->device_id, $type->name);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $result = $graph->getByType(-1, $type->name);
    }

    public function setUp(): void
    {
        if (!isset($this->graph)) {
            global $url,$token;

            $api = new ApiClient($url, $token);
            $this->device = $api->container->get(Device::class);
            $this->graph = $api->container->get(Graph::class);
        }
    }
}
