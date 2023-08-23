<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Calls.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @version 0.0.2
 */
class ApiClient
{
    public const API_PATH = '/api/v0';

    public Alert $alert;
    public AlertRule $alert_rule;
    public Arp $arp;
    public Component $component;
    public Curl $curl;
    public Device $device;
    public DeviceGroup $device_group;
    public Graph $graph;
    public Health $health;
    public Inventory $inventory;
    public Link $link;
    public Location $location;
    public Log $log;
    public Port $port;
    public AlertRule $alertRule;
    public Vlan $vlan;
    public Sensor $sensor;
    public System $system;
    public Wireless $wireless;

    protected string $url;
    protected string $token;

    /**
     * Initialize.
     */
    public function __construct(string $url, string $token)
    {
        $this->url = $url;
        $this->token = $token;

        $this->curl = new Curl($url, $token);

        $this->alert = new Alert($this);
        $this->alert_rule = new AlertRule($this);
        $this->arp = new Arp($this);
        $this->component = new Component($this);
        $this->device = new Device($this);
        $this->device_group = new DeviceGroup($this);
        $this->graph = new Graph($this);
        $this->health = new Health($this);
        $this->inventory = new Inventory($this);
        $this->link = new Link($this);
        $this->location = new Location($this);
        $this->log = new Log($this);
        $this->port = new Port($this);
        $this->vlan = new Vlan($this);
        $this->sensor = new Sensor($this);
        $this->system = new System($this);
        $this->wireless = new Wireless($this);
    }
}
