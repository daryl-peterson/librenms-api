<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Logs.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Log extends Common
{
    /**
     * Alert logs.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_alertlog
     */
    public function getAlertLogs(
        int|string $hostname = null,
        int $limit = null,
        int $start = null,
        string $from = null,
        string $to = null
    ): ?array {
        $url = $this->curl->getApiUrl('/logs/alertlog');

        $this->debug('GET ALERT LOG', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'url' => $url,
        ]);

        return $this->doRequest($url, $hostname, $limit, $start, $from, $to);
    }

    /**
     * Auth logs.
     *
     * @param int|string|null $hostname Hostname can be either the device hostname or id
     * @param int|null        $limit    The limit of results to be returned
     * @param int|null        $start    The page number to request
     * @param string|null     $from     The date and time or the event id to search from
     * @param string|null     $to       The data and time or the event id to search to
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_authlog
     */
    public function getAuthLogs(
        int|string $hostname = null,
        int $limit = null,
        int $start = null,
        string $from = null,
        string $to = null
    ): ?array {
        $url = $this->curl->getApiUrl('/logs/authlog');

        $this->debug('GET AUTHO LOGS', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'url' => $url,
        ]);

        return $this->doRequest($url, $hostname, $limit, $start, $from, $to);
    }

    /**
     * Event logs.
     *
     * @param int|string|null $hostname Hostname can be either the device hostname or id
     * @param int|null        $limit    The limit of results to be returned
     * @param int|null        $start    The page number to request
     * @param string|null     $from     The date and time or the event id to search from
     * @param string|null     $to       The data and time or the event id to search to
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_eventlog
     */
    public function getEventLogs(
        int|string $hostname = null,
        int $limit = null,
        int $start = null,
        string $from = null,
        string $to = null
    ): ?array {
        $url = $this->curl->getApiUrl('/logs/eventlog');

        $this->debug('GET EVENT LOGS', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'url' => $url,
        ]);

        return $this->doRequest($url, $hostname, $limit, $start, $from, $to);
    }

    /**
     * Sys logs.
     *
     * @param int|string|null $hostname Hostname can be either the device hostname or id
     * @param int|null        $limit    The limit of results to be returned
     * @param int|null        $start    The page number to request
     * @param string|null     $from     The date and time or the event id to search from
     * @param string|null     $to       The data and time or the event id to search to
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_syslog
     */
    public function getSysLogs(
        int|string $hostname = null,
        int $limit = null,
        int $start = null,
        string $from = null,
        string $to = null
    ): ?array {
        $url = $this->curl->getApiUrl('/logs/syslog');

        $this->debug('GET SYSLOG', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'url' => $url,
        ]);

        return $this->doRequest($url, $hostname, $limit, $start, $from, $to);
    }

    /**
     * Accept any json messages and passes to further syslog processing.
     * Single messages or an array of multiple messages is accepted.
     * See Syslog for more details and logstash integration.
     */
    public function syslogsink(array $data): bool
    {
        $url = $this->curl->getApiUrl('/syslogsink');
        $result = $this->curl->post($url, $data);
        $this->result = $result;

        return (!isset($result['status']) || 'ok' !== $result['status']) ? false : true;
    }

    private function doRequest(
        string $url,
        int|string $hostname = null,
        int $limit = null,
        int $start = null,
        string $from = null,
        string $to = null
    ): ?array {
        $params = [];
        $suffix = '';
        if (isset($hostname)) {
            $device = $this->getDeviceOrException($hostname);
            $url .= "/$device->device_id";
        }
        $params = ['limit' => $limit, 'start' => $start, 'from' => $from, 'to' => $to];
        foreach ($params as $key => $value) {
            if (isset($value)) {
                continue;
            }
            unset($params[$key]);
        }

        if (count($params) > 0) {
            $suffix = '?'.http_build_query($params);
        }
        $url .= $suffix;
        $this->result = $this->curl->get($url);

        $result = (!isset($this->result['logs']) || !isset($this->result['count']) || (0 === $this->result['count'])) ? null : $this->result['logs'];
        $this->debug('DO REQUEST', [
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => __LINE__,
            'url' => $url,
            'hostname' => $hostname,
            'params' => $params,
            'result' => $result,
            'org' => $this->result,
        ]);

        return $result;
    }
}
