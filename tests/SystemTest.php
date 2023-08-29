<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\System;
use PHPUnit\Framework\TestCase;

/**
 * Class description.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\System
 */
class SystemTest extends TestCase
{
    private System $system;

    public function testGet()
    {
        $sys = $this->system;
        $result = $sys->get();
        $this->assertIsArray($result);
    }

    public function testGetEndpoints()
    {
        $sys = $this->system;
        $result = $sys->getEndPoints();
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->system)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->system = $api->get(System::class);
        }
    }
}
