<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\Arp;
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
 * @covers \LibrenmsApiClient\Arp
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\DeviceCache
 */
class ArpTest extends TestCase
{
    private Arp $arp;

    public function testGet()
    {
        $arp = $this->arp;
        $result = $arp->get('169.198.0.1', '32');
        $this->assertNull($result);

        $result = $arp->get('0.0.0.0', '1');
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->arp)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->arp = $api->get(Arp::class);
        }
    }
}
