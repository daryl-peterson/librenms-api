<?php

/**
 * PHPUnit boot file.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */

declare(strict_types=1);

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';
$path = dirname(dirname(__FILE__));

use Dotenv\Dotenv;
use LibrenmsApiClient\Cache;
use LibrenmsApiClient\FileLogger;

$dotenv = Dotenv::createImmutable($path);
// $dotenv->safeLoad();
$dotenv->load();
$dotenv->required('TEST_LNMS_API_URL');

$cache = Cache::getInstance();
$cache->set(Cache::LOG_FILE, '/tmp/api-client.log');
$cache->set(Cache::LOG_LEVEL, FileLogger::DEBUG_LEVEL);
