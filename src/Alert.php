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

    public array|null $result;
    protected Curl $curl;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
    }

    /**
     * Get alert.
     *
     * @see https://docs.librenms.org/API/Alerts/#get_alert
     */
    public function get(int $id, bool $state = null): ?\stdClass
    {
        $params = [];
        $suffix = '';
        if (isset($state)) {
            $params['state'] = $state;
            $suffix = '?'.http_build_query($params);
        }

        $url = $this->curl->getApiUrl("/alerts/$id");
        $url .= $suffix;
        $this->result = $this->curl->get($url);

        return (!isset($this->result['alerts'][0]) || !is_object($this->result['alerts'][0])) ? null : $this->result['alerts'][0];
    }

    /**
     * Acknowledge alert.
     *
     * @see https://docs.librenms.org/API/Alerts/#ack_alert
     */
    public function acknowledge(int $id): bool
    {
        $url = $this->curl->getApiUrl("/alerts/$id");
        $this->result = $this->curl->put($url);

        $msg = strtoupper($this->result['message']);

        return (!isset($this->result) || str_contains($msg, 'NO ALERT')) ? false : true;
    }

    /**
     * Unmute alert.
     *
     * @see https://docs.librenms.org/API/Alerts/#unmute_alert
     */
    public function unmute(int $id): bool
    {
        $url = $this->curl->getApiUrl("/alerts/unmute/$id");
        $this->result = $this->curl->put($url);

        $msg = strtoupper($this->result['message']);

        return (!isset($this->result) || str_contains($msg, 'NO ALERT')) ? false : true;
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
        $suffix = $this->getQuery($state, $severity, $order, $alert_rule);

        try {
            $url = $this->curl->getApiUrl('/alerts');
            $url .= $suffix;
            $this->result = $this->curl->get($url);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();

            throw new ApiException($msg);
        }

        return (!isset($this->result['alerts']) || (0 === count($this->result['alerts']))) ? null : $this->result['alerts'];
    }

    private function getQuery(
        int $state,
        string $severity = null,
        string $order = null,
        int $alert_rule = null)
    {
        $suffix = '';
        $params = ['state' => $state, 'severity' => $severity, 'order' => $order, 'alert_rule' => $alert_rule];
        foreach ($params as $key => $value) {
            if (isset($value)) {
                continue;
            }
            unset($params[$key]);
        }
        $this->validateOrder($params);
        if (count($params) > 0) {
            $suffix = '?'.http_build_query($params);
        }

        return $suffix;
    }

    private function validateOrder(array &$params)
    {
        if (isset($params['order'])) {
            $order = strtolower($params['order']);
            if (in_array($order, ['asc', 'desc'])) {
                $params['order'] = "timestamp $order";
            }
        }
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
