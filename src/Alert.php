<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Client Alert.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Alert
{
    public const EXCEPTION_STATE = 'Invalid state parameter';
    public const EXCEPTION_SEVERITY = 'Invalid severity parameter';
    public const EXCEPTION_ORDER = 'Invalid order parameter';

    public AlertRule $rule;
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->rule = new AlertRule($this->api);
    }

    /**
     * Get alert.
     *
     * @see https://docs.librenms.org/API/Alerts/#get_alert
     */
    public function get(int $id): ?\stdClass
    {
        $url = $this->api->getApiUrl("/alerts/$id");
        $result = $this->api->get($url);

        if (!isset($result) || !isset($result['alerts'])) {
            return null;
        }

        if (0 !== count($result['alerts'])) {
            return $result['alerts'][0];
        }

        $result = $this->all();
        if (!isset($result[$id])) {
            return null;
        }

        return $result[$id];
    }

    /**
     * Acknowledge alert.
     *
     * @see https://docs.librenms.org/API/Alerts/#ack_alert
     */
    public function acknowledge(int $id): bool
    {
        $url = $this->api->getApiUrl("/alerts/$id");
        $result = $this->api->put($url);

        if (!isset($result) || !isset($result['code'])) {
            return false;
        }

        if (200 !== $result['code']) {
            return false;
        }

        return true;
    }

    /**
     * Unmute alert.
     *
     * @see https://docs.librenms.org/API/Alerts/#unmute_alert
     */
    public function unmute(int $id): bool
    {
        $url = $this->api->getApiUrl("/alerts/unmute/$id");
        $result = $this->api->put($url);

        if (!isset($result) || !isset($result['code'])) {
            return false;
        }

        if (200 !== $result['code']) {
            return false;
        }

        return true;
    }

    /**
     * Alert listing.
     *
     * @param int|null $state    0,1,2
     * @param string   $severity 'ok', 'warning', 'critical'
     * @param string   $order    null,'asc','desc'
     *
     * @see https://docs.librenms.org/API/Alerts/#list_alerts
     *
     * @throws ApiException
     */
    public function listing(
        int $state,
        string $severity = null,
        string $order = null,
        int $alert_rule = null
    ): ?array {
        $params = [];
        if (isset($state)) {
            if (!in_array($state, [0, 1, 2])) {
                throw new ApiException(self::EXCEPTION_STATE);
            }
            $params['state'] = $state;
        }

        if (isset($severity)) {
            $severity = strtolower($severity);
            if (!in_array($severity, ['ok', 'warning', 'critical'])) {
                throw new ApiException(self::EXCEPTION_SEVERITY);
            }
            $params['severity'] = $severity;
        }

        if (isset($order)) {
            $order = strtolower($order);
            if (!in_array($order, ['asc', 'desc'])) {
                throw new ApiException(self::EXCEPTION_ORDER);
            }
            $params['order'] = "timestamp $order";
        }

        if (isset($alert_rule)) {
            $params['alert_rule'] = $alert_rule;
        }
        $suffix = http_build_query($params);

        $url = $this->api->getApiUrl('/alerts?'.$suffix);
        $result = $this->api->get($url);

        if (!isset($result['alerts'])) {
            return null;
        }

        if (0 === count($result['alerts'])) {
            return null;
        }

        $alerts = [];
        foreach ($result['alerts'] as $alert) {
            $key = $alert->id;
            $alerts[$key] = $alert;
        }

        return $alerts;
    }

    /**
     * Get all alerts.
     */
    public function all(): ?array
    {
        $results = [];
        for ($i = 0; $i < 3; ++$i) {
            $tmp = $this->listing($i);
            if (is_array($tmp)) {
                $results = array_replace($results, $tmp);
            }
        }
        if (0 === count($results)) {
            return null;
        }

        ksort($results);

        return $results;
    }
}
