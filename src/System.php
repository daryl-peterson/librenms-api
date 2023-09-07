<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Client System.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 *
 * @todo unit test
 */
class System
{
    protected Curl $curl;
    public array|null $result;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
        $this->result = [];
    }

    /**
     * Get LibreNMS system information.
     *
     * @see https://docs.librenms.org/API/System/
     */
    public function get(): ?array
    {
        $url = $this->curl->getApiUrl('/system');
        $this->result = $this->curl->get($url);

        return (!isset($this->result) || !isset($this->result['system'])) ? null : $this->result['system'];
    }

    /**
     * Get api end point list.
     */
    public function getEndPoints(): ?array
    {
        $url = $this->curl->getApiUrl('');
        $this->result = $this->curl->get($url);
        $passed = (!isset($this->result['code']) || (200 !== $this->result['code'])) ? false : true;

        unset($this->result['headers']);
        unset($this->result['code']);

        return (!$passed) ? null : $this->result;
    }
}
