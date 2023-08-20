<?php

use LibrenmsApiClient\ApiClient;

require_once 'vendor/autoload.php';

$url = readline('Enter URL : ');
$token = readline('Enter Token : ');

$api = new ApiClient($url, $token);
/*
$rules = [];
$devices[] = 1;
$rules = [
    'id' => 'devices.hostname',
    'field' => 'devices.hostname',
    'type' => 'string',
    'input' => 'text',
    'operator' => 'equal',
    'value' => 'localhost',
];
$rules = (object) $rules;
$builder = [
    'condition' => 'AND',
    'rules' => [$rules],
    'valid' => true,
];

try {
    $result = $api->alert->rule->add($devices, 'test', $builder, 'critical', true);
} catch (\Throwable $th) {
    echo $th->getMessage();
    echo $th->getTraceAsString();
}

$result = $api->alert->rule->listing();
print_r($result);

$rule = $api->alert->rule->getByName('test');
print_r($rule);

$result = $api->alerts->rules->delete($rule->id);
var_dump($result);
*/
$result = $api->sensor->get(1);
print_r($result);

$result = $api->vlan->listing();
print_r($result);
