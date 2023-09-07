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
    private $logLevel;
    private $logFile;
    private Cache $cache;

    public function __destruct()
    {
        $cache = Cache::getInstance();
        $cache->set(Cache::LOG_LEVEL, $this->logLevel);
        $cache->set(Cache::LOG_FILE, $this->logFile);
    }

    public function testLog()
    {
        $obj = $this->logger;
        $result = $obj->debug('TEST DEBUG');
        $this->assertNull($result);

        $result = $obj->error('TEST ERROR', ['UNIT' => $this]);
        $this->assertNull($result);

        $result = $obj->log('BLAH', 'TEST ERROR', ['UNIT' => $this]);
        $this->assertNull($result);
    }

    public function setUp(): void
    {
        $cache = Cache::getInstance();
        $this->logLevel = $cache->get(Cache::LOG_LEVEL);
        $this->logFile = $cache->get(Cache::LOG_FILE);
        $cache->delete(Cache::LOG_LEVEL);
        $cache->delete(Cache::LOG_FILE);

        $this->logger = new FileLogger();
    }
}
