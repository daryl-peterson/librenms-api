<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Curl;
use LibrenmsApiClient\DeviceGroup;
use PHPUnit\Framework\TestCase;

/**
 * Class description.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\Curl
 * @covers \LibrenmsApiClient\DeviceGroup
 */
class DeviceGroupTest extends TestCase
{
    private DeviceGroup $group;
    private string $name;

    public function testAdd()
    {
        $group = $this->group;

        $this->expectException(ApiException::class);
        $group->add($this->name, false, 'blah', null, null);

        $this->expectException(ApiException::class);
        $group->add($this->name, true, 'BLAH');
    }

    public function testGetListing()
    {
        $group = $this->group;
        $result = $group->getListing();
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->group)) {
            global $url,$token;

            $curl = new Curl($url, $token);
            $this->name = 'TEST RULE';
            $this->group = new DeviceGroup($curl);
        }
    }
}
