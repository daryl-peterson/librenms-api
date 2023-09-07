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

        if (!isset($result['status']) || 'ok' !== $result['status']) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    public function getResult(): ?array
    {
        return $this->result;
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
            $device = $this->getDevice($hostname);
            if (!isset($device)) {
                throw new ApiException(ApiException::ERR_DEVICE_NOT_EXIST);
            }
            $url .= "/$device->device_id";
        }

        if (isset($limit)) {
            $params['limit'] = $limit;
        }

        if (isset($start)) {
            $params['start'] = $start;
        }

        if (isset($from)) {
            $params['from'] = $start;
        }

        if (isset($to)) {
            $params['to'] = $to;
        }

        if (count($params) > 0) {
            $suffix = '?'.http_build_query($params);
        }
        $url .= $suffix;

        $result = $this->curl->get($url);
        $this->result = $result;

        if (!isset($result['logs']) || !isset($result['count'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }
        if (0 === $result['count']) {
            return null;
        }
        unset($result['headers']);
        unset($result['code']);
        unset($result['status']);

        return $result;
    }
}
