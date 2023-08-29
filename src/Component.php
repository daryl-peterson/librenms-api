<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Component.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Component extends Common
{
    protected Curl $curl;
    public array|null $result;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
        $this->result = [];
    }

    /**
     * Create a new component of a type on a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#add_components
     */
    public function add(int|string $hostname, string $type): ?\stdClass
    {
        $type = rawurlencode($type);
        $url = $this->curl->getApiUrl("/devices/$hostname/components/$type");
        $this->result = $this->curl->post($url);

        if (!isset($this->result['components']) || !is_object($this->result['components'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $components = $this->result['components'];
        foreach ($components as $id => $props) {
            $return = (object) [
                'id' => $id,
                'type' => $props->type,
                'label' => $props->label,
                'status' => $props->status,
                'ignore' => $props->ignore,
                'error' => $props->error,
            ];
        }

        return $return;
    }

    /**
     * Edit an existing component on a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#edit_components
     *
     * @throws ApiException Triggered by curl->put
     */
    public function edit(int|string $hostname, int $component_id, array $data): bool
    {
        $obj = new \stdClass();
        $obj->$component_id = (object) $data;

        $url = $this->curl->getApiUrl("/devices/$hostname/components");
        $this->result = $this->curl->put($url, $obj);
        if (!isset($this->result['code'])) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * Delete an existing component on a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     *
     * @see https://docs.librenms.org/API/Devices/#delete_components
     *
     * @throws ApiException Triggered by curl->delete if item does not exist
     */
    public function delete(int|string $hostname, int $component_id): bool
    {
        $url = $this->curl->getApiUrl("/devices/$hostname/components/$component_id");
        $this->result = $this->curl->delete($url);

        if (!isset($this->result['code']) || (200 !== $this->result['code'])) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * Get a list of components for a particular device.
     *
     * @param int|string $hostname Hostname can be either the device hostname or id
     * @param int        $id       Component id
     *
     * @see https://docs.librenms.org/API/Devices/#get_components
     */
    public function get(
        int|string $hostname,
        int $id = null,
        string $type = null,
        string $label = null,
        bool $status = null,
        bool $disable = null,
        bool $ignore = null
    ): ?\stdClass {
        $url = $this->curl->getApiUrl("/devices/$hostname/components");

        $params = [];
        $suffix = '';
        if (isset($id)) {
            $params['id'] = $id;
        }
        if (isset($type)) {
            $params['type'] = $type;
        }

        if (isset($label)) {
            $params['label'] = $label;
        }
        if (isset($status)) {
            $params['status'] = $status;
        }
        if (isset($disable)) {
            $params['disable'] = $disable;
        }
        if (isset($ignore)) {
            $params['ignore'] = $ignore;
        }

        if (count($params) > 0) {
            $suffix = '?'.http_build_query($params);
        }
        $url .= $suffix;

        $this->result = $this->curl->get($url);

        if (!isset($this->result['components'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['components'];
    }
}
