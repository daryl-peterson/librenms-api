<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Location.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Location
{
    private ApiClient $api;
    private Curl $curl;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
        $this->curl = $this->api->curl;
    }

    /**
     * Add location.
     *
     * @see https://docs.librenms.org/API/Locations/#add_location
     */
    public function add(string $location, string $lat, string $lng, bool $fixed = false): ?array
    {
        $data = [
            'location' => $location,
            'lat' => $lat,
            'lng' => $lng,
            'fixed_coordinates' => $fixed,
        ];
        $url = $this->curl->getApiUrl('/locations');
        $result = $this->curl->post($url, $data);

        if (!isset($result) || !isset($result['message'])) {
            return null;
        }
        unset($result['headers']);

        return $result;
    }

    /**
     * Get location.
     *
     * @see https://docs.librenms.org/API/Locations/#get_location
     */
    public function get(string $location): ?\stdClass
    {
        $location = rawurlencode($location);
        $url = $this->curl->getApiUrl("/location/$location");

        $result = $this->curl->get($url);
        if (!isset($result['get_location'])) {
            return null;
        }

        return $result['get_location'];
    }

    /**
     * Get locations list.
     *
     * @see https://docs.librenms.org/API/Locations/#list_locations
     */
    public function getListing(): ?array
    {
        $url = $this->curl->getApiUrl('/resources/locations');
        $result = $this->curl->get($url);
        if (!isset($result) || !isset($result['locations'])) {
            return null;
        }

        return $result['locations'];
    }

    /**
     * Delete location.
     *
     * @see https://docs.librenms.org/API/Locations/#delete_location
     */
    public function delete(string $location): ?array
    {
        $location = rawurlencode($location);
        $url = $this->curl->getApiUrl("/locations/$location");

        $result = $this->curl->delete($url);

        if (!isset($result) || !isset($result['message'])) {
            return null;
        }
        unset($result['headers']);

        return $result;
    }

    /**
     * Update location.
     *
     * @see https://docs.librenms.org/API/Locations/#edit_location
     */
    public function edit(string $location, array $data): ?array
    {
        $location = rawurlencode($location);
        $url = $this->curl->getApiUrl("/locations/$location");
        $result = $this->curl->patch($url, $data);

        return $result;
    }
}
