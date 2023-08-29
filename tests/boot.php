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

$dir = dirname(__FILE__);
if (file_exists($dir.'/config.php')) {
    require_once $dir.'/config.php';
} else {
    global $url, $token, $settings;

    $url = readline('URL       : ');
    $token = readline('TOKEN     : ');
    $device_id = readline('DEVICE ID : ');
    $hostname = readline('HOSTNAME  : ');

    $settings = [
        'url' => $url,
        'token' => $token,
        'device_id' => $device_id,
        'device_hostname' => $hostname,
    ];
}
