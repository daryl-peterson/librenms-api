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
    $router_id = readline('ROUTER ID : ');
    $router_hostname = readline('ROUTER HOSTNAME  : ');
    $switch_id = readline('SWITCH ID  : ');
    $test_add_ip = readline('TEST ADD IP (MUST BE PINGABLE) : ');
    $test_add_gw = readline('TEST ADD GATEWAY (MUST BE PINGABLE) : ');

    $settings = [
        'url' => $url,
        'token' => $token,
        'router_id' => $router_id,
        'router_hostname' => $router_hostname,
        'switch_id' => $switch_id,
        'test_add_ip' => $test_add_ip,
        'test_add_gw' => $test_add_gw,
    ];
}
