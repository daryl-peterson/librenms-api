<?php

namespace LibrenmsApiClient;

/*

{
   "devices":[
      1,
      2,
      3
   ],
   "name":"testrule",
   "builder":{
      "condition":"AND",
      "rules":[
         {
            "id":"devices.hostname",
            "field":"devices.hostname",
            "type":"string",
            "input":"text",
            "operator":"equal",
            "value":"localhost"
         }
      ],
      "valid":true
   },
   "severity":"critical",
   "count":15,
   "delay":"5 m",
   "interval":"5 m",
   "mute":false
}

*/

/**
 * Class description.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class AlertRuleBuilder
{
    protected int $id;

    public string $name;
    public array $devices;
    public string $severity;
    public bool $disabled;
    public int $count;
    public string $delay;
    public string $interval;
    public bool $mute;

    public function __construct()
    {
        $this->devices = [];
    }

    public function addDevices(array $device_ids)
    {
        $this->devices = array_replace($this->devices, $device_ids);
    }

    public function addCondition()
    {
    }
}
