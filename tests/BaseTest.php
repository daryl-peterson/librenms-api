<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\Curl;
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
 */
class BaseTest extends TestCase
{
    protected ApiClient $api;
    protected Curl $curl;
    protected string $url;
    protected string $token;
    protected int $routerId;
    protected string $routerIfName;
    protected int $switchId;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->url = $_ENV['TEST_LNMS_API_URL'];
        $this->token = $_ENV['TEST_LNMS_API_TOKEN'];
        $this->routerId = $_ENV['TEST_LNMS_API_ROUTER_ID'];
        $this->routerIfName = $_ENV['TEST_LNMS_API_ROUTER_IFNAME'];
        $this->switchId = $_ENV['TEST_LNMS_API_SWITCH_ID'];

        $this->api = new ApiClient($this->url, $this->token);
        $this->curl = $this->api->get(Curl::class);
    }

    public function testSelf()
    {
        $this->assertIsObject($this->api);
    }
}
