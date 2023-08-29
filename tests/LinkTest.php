<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Link;
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
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\Device
 * @covers \LibrenmsApiClient\Link
 * @covers \LibrenmsApiClient\Common
 */
class LinkTest extends TestCase
{
    private ApiClient $api;
    private Link $link;

    public function testGetById()
    {
        $link = $this->link;
        $result = $link->getListing();
        $object = array_pop($result);

        $result = $link->getById((int) $object->id);
        $this->assertIsObject($result);

        $this->expectException(ApiException::class);
        $result = $link->getById(0);
    }

    public function testGetByHost()
    {
        $link = $this->link;
        $result = $link->getListing();
        $object = array_pop($result);

        $result = $link->getDeviceLinks($object->local_device_id);
        $this->assertIsArray($result);

        $result = $link->getDeviceLinks(0);
        $this->assertFalse($result);
    }

    public function testGetListing()
    {
        $link = $this->link;
        $result = $link->getListing();
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->api)) {
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);
            $this->link = $api->get(Link::class);
        }
    }
}
