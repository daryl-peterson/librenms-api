<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\Location;
use PHPUnit\Framework\TestCase;

/**
 * Location API Unit tests.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 */
class LocationTest extends TestCase
{
    private ApiClient $api;

    public function testListing()
    {
        $result = $this->api->location->getListing();
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        if (!isset($this->api)) {
            global $url,$token;

            $this->api = new ApiClient($url, $token);
        }
    }
}
