<?php

declare(strict_types=1);

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

global $url, $token;

$url = readline('Enter URL : ');
$token = readline('Enter Token : ');
