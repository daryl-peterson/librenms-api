<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Alert Rules.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.2
 */
class AlertRule
{
    private ApiClient $api;
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;
    }

    /**
     * Add alert rule.
     *
     * @param mixed $devices Array of device ids or -1
     *
     * @see https://docs.librenms.org/API/Alerts/#add_rule
     */
    public function add(
            mixed $devices,
            string $name,
            array $builder,
            string $severity,
            bool $disabled = false,
            int $count = 0,
            string $delay = '5 m',
            string $interval = '5 m',
            bool $mute = false
        ): bool {
        $data['devices'] = $devices;
        $data['name'] = $name;
        $data['builder'] = $builder;
        $data['severity'] = $severity;
        $data['disabled'] = $disabled;
        $data['count'] = $count;
        $data['delay'] = $delay;
        $data['interval'] = $interval;
        $data['mute'] = $mute;

        $url = $$this->curl->getApiUrl('/rules');
        $result = $$this->curl->post($url, $data);

        if (!isset($result) || !isset($result['code'])) {
            return false;
        }
        if (200 === !$result['code']) {
            return false;
        }

        return true;
    }

    /**
     * Get alert rule.
     *
     * @see https://docs.librenms.org/API/Alerts/#get_alert_rule
     */
    public function get(int $id): ?\stdClass
    {
        $url = $this->curl->getApiUrl("/rules/$id");
        $result = $this->curl->get($url);

        if (!isset($result['rules'][0])) {
            return null;
        }

        return $result['rules'][0];
    }

    /**
     * Get alert rule by name.
     */
    public function getByName(string $name): ?\stdClass
    {
        $rules = $this->getListing();

        $result = null;
        foreach ($rules as $rule) {
            if (strtolower($rule->name) === strtolower($name)) {
                $result = $rule;
                break;
            }
        }

        return $result;
    }

    public function getBuilder(): AlertRuleBuilder
    {
        return new AlertRuleBuilder();
    }

    /**
     * Delete alert rule.
     *
     * @see https://docs.librenms.org/API/Alerts/#delete_rule
     */
    public function delete(int $id): bool
    {
        $url = $$this->curl->getApiUrl("/rules/$id");
        $result = $$this->curl->delete($url);

        if (!isset($result) || !isset($result['code'])) {
            return false;
        }
        if (200 !== $result['code']) {
            return false;
        }

        return true;
    }

    /**
     * List alert rules.
     *
     * @see https://docs.librenms.org/API/Alerts/#list_alert_rules
     */
    public function getListing(): ?array
    {
        $url = $$this->curl->getApiUrl('/rules');
        $result = $$this->curl->get($url);
        if (!isset($result['rules'])) {
            return null;
        }

        if (0 === count($result['rules'])) {
            return null;
        }

        return $result['rules'];
    }

    public function edit()
    {
    }
}
