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
    private Curl $curl;
    public array|null $result;

    public function __construct(Curl $curl)
    {
        $this->curl = $curl;
        $this->result = [];
    }

    /**
     * Add location.
     *
     * @see https://docs.librenms.org/API/Locations/#add_location
     *
     * @throws ApiException Triggered by curl->post
     */
    public function add(string $location, string $lat, string $lng, bool $fixed = false): bool
    {
        $location = trim($location);
        $data = [
            'location' => $location,
            'lat' => $lat,
            'lng' => $lng,
            'fixed_coordinates' => $fixed,
        ];

        $url = $this->curl->getApiUrl('/locations');
        $this->result = $this->curl->post($url, $data);

        if (!isset($this->result['status']) || ('ok' !== $this->result['status'])) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * Get location.
     *
     * @param int|string $location Name or id of the location
     *
     * @see https://docs.librenms.org/API/Locations/#get_location
     *
     * @throws ApiException Triggered by curl->get
     */
    public function get(int|string $location): false|\stdClass
    {
        $location = $this->fixLocation($location);
        $url = $this->curl->getApiUrl("/location/$location");
        $this->result = $this->curl->get($url);

        if (!isset($this->result['get_location']) || !is_object($this->result['get_location'])) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['get_location'];
    }

    /**
     * Get locations list.
     *
     * @see https://docs.librenms.org/API/Locations/#list_locations
     */
    public function getListing(): ?array
    {
        $url = $this->curl->getApiUrl('/resources/locations');
        $this->result = $this->curl->get($url);

        if (!isset($this->result['locations'])) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->result['locations'];
    }

    /**
     * Delete location.
     *
     * @param int|string $location Name or id of the location
     *
     * @see https://docs.librenms.org/API/Locations/#delete_location
     *
     * @throws ApiException Triggered by curl->delete
     */
    public function delete(int|string $location): bool
    {
        $location = $this->fixLocation($location);
        $url = $this->curl->getApiUrl("/locations/$location");
        $this->result = $this->curl->delete($url);

        if (!isset($this->result['code']) || (201 !== $this->result['code'])) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * Update location.
     *
     * @param int|string $location Name or id of the location
     *
     * @see https://docs.librenms.org/API/Locations/#edit_location
     *
     * @throws ApiException Triggered by curl->patch
     */
    public function edit(
        int|string $location,
        string $name = null,
        string $lat = null,
        string $lng = null,
        bool $fixed = null
    ): bool {
        $data = [];

        if (isset($name)) {
            $data['location'] = trim($name);
        }
        if (isset($lat)) {
            $data['lat'] = $lat;
        }
        if (isset($lng)) {
            $data['lng'] = $lng;
        }
        if (isset($fixed)) {
            $data['fixed_coordinates'] = (bool) $fixed;
        }

        $location = $this->fixLocation($location);
        $url = $this->curl->getApiUrl("/locations/$location");
        $this->result = $this->curl->patch($url, $data);

        if (!isset($this->result['code']) || (201 !== $this->result['code'])) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    private function fixLocation(int|string $location)
    {
        if (is_int($location)) {
            return $location;
        }

        return rawurlencode(trim($location));
    }
}
