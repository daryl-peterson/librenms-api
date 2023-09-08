<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Curl;

/**
 * Test Curl.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\ApiClient
 * @covers \LibrenmsApiClient\Curl
 */
class CurlTest extends BaseTest
{
    public function testResovleFail()
    {
        $curl = new Curl('https://blah.example.com', $this->token);
        $this->expectExceptionMessage('Could not resolve host');
        $url = $curl->getApiUrl('');
        $result = $this->curl->get($url);
    }

    public function testFailConnect()
    {
        $curl = new Curl('https://google.com:2222', $this->token);
        $this->expectExceptionMessage('Could not connect');
        $url = $curl->getApiUrl('');
        $this->curl->get($url);
    }

    public function testBadToken()
    {
        $token = 'blahblahblah';
        $curl = new Curl($this->url, $token);
        $this->expectExceptionMessage('Unauthenticated.');
        $url = $curl->getApiUrl('');
        $curl->get($url);
    }

    public function testPatch()
    {
        $this->expectException(ApiException::class);
        $this->curl->patch('', []);
    }

    public function testPut()
    {
        $this->expectException(ApiException::class);
        $this->curl->put('', []);
    }

    public function testPost()
    {
        $this->expectException(ApiException::class);
        $this->curl->post('', []);
    }

    public function testDelete()
    {
        $this->expectException(ApiException::class);
        $this->curl->delete('');
    }

    public function setUp(): void
    {
        if (!isset($this->curl)) {
            $this->curl = $this->api->get(Curl::class);
        }
    }
}
