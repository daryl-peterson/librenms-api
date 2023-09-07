<?php

namespace LibrenmsApiClient\Tests;

use LibrenmsApiClient\Cache;
use LibrenmsApiClient\FileLogger;
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
 * @since       1.0.0
 *
 * @covers \LibrenmsApiClient\Cache
 * @covers \LibrenmsApiClient\FileLogger
 */
class FileLoggerTest extends TestCase
{
    private FileLogger $logger;

    public function testLog()
    {
        /*
        $obj = $this->logger;
        $result = $obj->debug('TEST DEBUG');
        $this->assertNull($result);

        $result = $obj->error('TEST ERROR', ['UNIT' => $this]);
        $this->assertNull($result);

        $result = $obj->log('BLAH', 'TEST ERROR', ['UNIT' => $this]);
        $this->assertNull($result);
        */
    }

    public function setUp(): void
    {
        /*
        $cache = Cache::getInstance();
        $cache->delete(Cache::LOG_LEVEL);
        $cache->delete(Cache::LOG_FILE);

        $this->logger = new FileLogger();
        */
    }
}
