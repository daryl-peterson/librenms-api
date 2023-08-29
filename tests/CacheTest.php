<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\Cache;
use PHPUnit\Framework\TestCase;

/**
 * Cache unit tests.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @covers \LibrenmsApiClient\Cache
 */
class CacheTest extends TestCase
{
    private Cache $cache;

    public function test()
    {
        $cache = $this->cache;
        $cache->set('TEST KEY', 'TEST VALUE');
        $result = $cache->exists('TEST KEY');
        $this->assertTrue($result);

        $result = $cache->get('TEST KEY');
        $this->assertIsString($result);

        $result = $cache->exists('INVALID KEY');
        $this->assertFalse($result);

        $result = $cache->get('INVALID KEY');
        $this->assertNull($result);

        $cache->delete('TEST KEY');
    }

    public function setUp(): void
    {
        if (!isset($this->cache)) {
            $this->cache = Cache::getInstance();
        }
    }
}
