<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
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
 * @covers \LibrenmsApiClient\Common
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\FileLogger
 * @covers \LibrenmsApiClient\Graph
 * @covers \LibrenmsApiClient\DeviceCache
 * @covers \LibrenmsApiClient\PortCache
 */
class GraphTest extends TestCase
{
    private Graph $graph;
    private int $router_id;

    public function testGetPort()
    {
        $graph = $this->graph;

        $device = $graph->getDevice($this->router_id);
        $this->assertIsObject($device);

        $result = $graph->getPort($this->router_id);
        $this->assertIsArray($result);

        $this->expectException(ApiException::class);
        $graph->getPort(0);

        $result = $graph->getPort($this->router_id, ['lo0']);
        $this->assertNull($result);
    }

    public function testGetByType()
    {
        $graph = $this->graph;

        $result = $graph->getTypes($this->router_id);
        $this->assertIsArray($result);
        $type = array_pop($result);

        $result = $graph->getByType($this->router_id, $type->name);
        $this->assertIsArray($result);

        $result = $graph->getByType(0, $type->name);
        $this->assertNull($result);

        $from = date('m/d/Y 00:00');
        $to = date('m/d/Y 12:00');
        $result = $graph->getByType($this->router_id, $type->name, $from, $to, 'display');
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->graph)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->graph = $api->get(Graph::class);
            $this->router_id = $settings['router_id'];
        }
    }
}
