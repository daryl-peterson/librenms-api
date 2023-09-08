<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\DeviceGroup;

/**
 * Class description.
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
 * @covers \LibrenmsApiClient\DeviceGroup
 */
class DeviceGroupTest extends BaseTest
{
    private DeviceGroup $group;
    private string $groupName;

    public function testAdd()
    {
        $group = $this->group;

        $this->expectException(ApiException::class);
        $group->add($this->groupName, false, 'blah', null, null);

        $this->expectException(ApiException::class);
        $group->add($this->groupName, true, 'BLAH');
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
            $this->groupName = 'TEST RULE';
            $this->group = $this->api->get(DeviceGroup::class);
        }
    }
}
