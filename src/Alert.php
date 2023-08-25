<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Client Alert.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
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
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;
    }

    /**
     * Get alert.
     *
     * @see https://docs.librenms.org/API/Alerts/#get_alert
     */
    public function get(int $id, bool $state = null): ?\stdClass
    {
        $params = [];
        $suffix = false;
        if (isset($state)) {
            $params['state'] = $state;
        }
        if (count($params) > 0) {
            $suffix = http_build_query($params);
        }

        $url = $this->curl->getApiUrl("/alerts/$id");
        if ($suffix) {
            $url .= "?$suffix";
        }

        $result = $this->curl->get($url);

        if (!isset($result['alerts'][0]) || !is_object($result['alerts'][0])) {
            return null;
        }

        return $result['alerts'][0];
    }

    /**
     * Acknowledge alert.
     *
     * @see https://docs.librenms.org/API/Alerts/#ack_alert
     */
    public function acknowledge(int $id): bool
    {
        $url = $this->curl->getApiUrl("/alerts/$id");
        $result = $this->curl->put($url);

        $msg = strtoupper($result['message']);

        if (!isset($result) || str_contains($msg, 'NO ALERT')) {
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
        $url = $this->curl->getApiUrl("/alerts/unmute/$id");
        $result = $this->curl->put($url);

        $msg = strtoupper($result['message']);

        if (!isset($result) || str_contains($msg, 'NO ALERT')) {
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
    public function getListing(
        int $state,
        string $severity = null,
        string $order = null,
        int $alert_rule = null
    ): ?array {
        $params = [];
        $suffix = false;
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
        if (count($params) > 0) {
            $suffix = http_build_query($params);
        }

        $url = $this->curl->getApiUrl('/alerts');
        if ($suffix) {
            $url .= "?$suffix";
        }
        $result = $this->curl->get($url);

        if (!isset($result['alerts']) || (0 === count($result['alerts']))) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
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
    public function all(): array
    {
        $results = [];
        for ($i = 0; $i < 3; ++$i) {
            $tmp = $this->getListing($i);
            if (is_array($tmp)) {
                $results = array_replace($results, $tmp);
            }
        }

        ksort($results);

        return $results;
    }
}
