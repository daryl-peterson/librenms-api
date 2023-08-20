<?php

namespace LibrenmsApiClient;

/**
 * Class description.
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.1
 */
class Port
{
    private ApiClient $api;

    public function __construct(ApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Undocumented function.
     *
     * @see https://docs.librenms.org/API/Ports/#get_port_info
     */
    public function get(int $id)
    {
        $url = $this->api->getApiUrl("/ports/$id");
        $result = $this->api->get($url);

        if (!isset($result['port'][0])) {
            return null;
        }

        return $result['port'][0];
    }

    /**
     * Get port listing.
     *
     * @see https://docs.librenms.org/API/Ports/#get_all_ports
     */
    public function listing(): ?array
    {
        $columns = urlencode('device_id,port_id,deleted,ifName,ifDescr,ifAlias,ifMtu,ifType,ifSpeed,ifOperStatus,ifAdminStatus,ifPhysAddress,ifInErrors,ifOutErrors');
        $url = $this->api->getApiUrl('/ports?columns='.$columns);
        $result = $this->api->get($url);

        if (!isset($result['ports'])) {
            return null;
        }

        return $result['ports'];
    }

    /**
     * Undocumented function.
     *
     * @see https://docs.librenms.org/API/Ports/#search_ports
     */
    public function search()
    {
    }
}
