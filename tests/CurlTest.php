<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\ApiClient;
use LibrenmsApiClient\ApiException;
use LibrenmsApiClient\Curl;
use PHPUnit\Framework\TestCase;

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
class CurlTest extends TestCase
{
    private Curl $curl;
    private string $url;
    private string $token;

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
            global $settings;

            $api = new ApiClient($settings['url'], $settings['token']);

            $this->url = $settings['url'];
            $this->token = $settings['token'];
            $this->curl = $api->get(Curl::class);
        }
    }
}
