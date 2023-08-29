<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
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
 * @covers \LibrenmsApiClient\Cache
 * @covers \LibrenmsApiClient\Graph
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Common
 */
class GraphTest extends TestCase
{
    private Graph $graph;
    private int $deviceId;

    public function testGetPort()
    {
        $graph = $this->graph;

        $device = $graph->getDevice($this->deviceId);
        $this->assertIsObject($device);

        $result = $graph->getPort($this->deviceId);
        $this->assertIsArray($result);

        $result = $graph->getPort(0);
        $this->assertNull($result);

        $result = $graph->getPort($this->deviceId, ['lo0']);
        $this->assertNull($result);
    }

    public function testGetByType()
    {
        $graph = $this->graph;

        $result = $graph->getTypes($this->deviceId);
        $this->assertIsArray($result);
        $type = array_pop($result);

        $result = $graph->getByType($this->deviceId, $type->name);
        $this->assertIsArray($result);

        $result = $graph->getByType(0, $type->name);
        $this->assertNull($result);

        $from = date('m/d/Y 00:00');
        $to = date('m/d/Y 12:00');
        $result = $graph->getByType($this->deviceId, $type->name, $from, $to, 'display');
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->graph)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->graph = $api->get(Graph::class);
            $this->deviceId = $settings['device_id'];
        }
    }
}
