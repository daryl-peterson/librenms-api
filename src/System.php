<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Client System.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class System
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Get LibreNMS system information.
     *
     * @see https://docs.librenms.org/API/System/
     */
    public function get(): ?array
    {
        $url = $this->api->getApiUrl('/system');
        $result = $this->api->get($url);

        if (!isset($result) || !isset($result['system'])) {
            return null;
        }

        return $result['system'];
    }

    /**
     * Get api end point list.
     */
    public function endPoints(): ?array
    {
        $url = $this->api->getApiUrl('');
        $result = $this->api->get($url);
        if (!isset($result['code'])) {
            return null;
        }
        if (200 !== $result['code']) {
            return null;
        }

        unset($result['headers']);
        unset($result['code']);

        return $result;
    }
}
