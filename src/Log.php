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
class Log
{
    private ApiClient $api;
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $api->curl;
    }

    /**
     * Alert logs.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_alertlog
     */
    public function getAlert(int|string $hostname): ?array
    {
        $url = $this->curl->getApiUrl("/logs/alertlog/$hostname");
        $result = $this->curl->get($url);

        if (!isset($result['logs'])) {
            return null;
        }

        if (0 === count($result['logs'])) {
            return null;
        }

        return $result['logs'];
    }

    /**
     * Auth logs.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_authlog
     */
    public function getAuth(int|string $hostname): ?array
    {
        $url = $this->curl->getApiUrl("/logs/authlog/$hostname");
        $result = $this->curl->get($url);

        if (!isset($result['logs'])) {
            return null;
        }

        if (0 === count($result['logs'])) {
            return null;
        }

        return $result['logs'];
    }

    /**
     * Event logs.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_eventlog
     */
    public function getEvent(int|string $hostname): ?array
    {
        $url = $this->curl->getApiUrl("/logs/eventlog/$hostname");
        $result = $this->curl->get($url);

        if (!isset($result['logs'])) {
            return null;
        }

        if (0 === count($result['logs'])) {
            return null;
        }

        return $result['logs'];
    }

    /**
     * Sys logs.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @return array|null Array of stdClass Objects
     *
     * @see https://docs.librenms.org/API/Logs/#list_syslog
     */
    public function sys(int|string $hostname): ?array
    {
        $url = $this->curl->getApiUrl("/logs/syslog/$hostname");
        $result = $this->curl->get($url);

        if (!isset($result['logs'])) {
            return null;
        }

        return $result['logs'];
    }

    /**
     * Accept any json messages and passes to further syslog processing.
     * Single messages or an array of multiple messages is accepted.
     * See Syslog for more details and logstash integration.
     *
     * @todo finish function
     */
    public function syslogsink()
    {
    }
}
