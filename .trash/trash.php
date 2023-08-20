<?php

    /**
     * Get list of device models.
     */
    public function getDeviceModels(): ?array
    {
        $list = $this->getDeviceList();

        if (!isset($list)) {
            return null;
        }

        $result = [];
        $keys = ['icon'];
        $blank = ['icon' => '', 'version' => '', 'hardware' => ''];
        foreach ($list['org'] as $device) {
            $device = (array) $device;
            $device = array_merge($blank, $device);

            if (isset($device['icon'])) {
                $mfr = explode('.', $device['icon']);
                $mfr = strtoupper($mfr[0]);
            }

            if (!isset($mfr) || empty($mfr)) {
                $mfr = 'UNKNOWN';
            }
            $hardware = $device['hardware'];

            if (!isset($hardware) || empty($hardware)) {
                continue;
            }

            if (isset($result[$mfr][$hardware])) {
                $cnt = $result[$mfr][$hardware];
                ++$cnt;
            } else {
                $cnt = 1;
            }
            $result[$mfr][$hardware] = $cnt;
        }

        return $result;
    }




    /**
     * Get graph.
     *
     * get_graph_by_port_hostname => /api/v0/devices/{hostname}/ports/{ifname}/{type}.
     */
    public function getGraph(array $interfaces = null, string $type = null, int $id = null, string $ip = null, string $host = null): ?array
    {
        $result = [];
        if (!isset($type)) {
            $type = 'port_bits';
        }
        $device = $this->getDevice($id, $ip, $host);
        if (!isset($device) || !isset($device['hostname'])) {
            return null;
        }

        if (isset($interfaces)) {
            $ports = $this->fixInterfaceArray($interfaces);
        } else {
            $ports = $this->getPortsByDevice($device['device_id']);
        }

        if (!isset($ports)) {
            return null;
        }

        foreach ($ports as $port) {
            $deviceId = $device['device_id'];
            $url = $this->getApiUrl("/devices/$deviceId/ports/".urlencode($port->ifName)."/$type");

            // Make sure response is an array
            $response = $this->doCurlGet($url, false);

            if (!is_array($response)) {
                continue;
            }
            $result[$port->ifName] = $response['image'];
        }

        if (0 === count($result)) {
            return null;
        }

        return $result;
    }

    /**
     * Get graph types
     * get_graphs => /api/v0/devices/{hostname}/graphs.
     */
    public function getGraphType(int $id = null, string $ip = null, string $host = null): ?array
    {
        $device = $this->getDevice($id, $ip, $host);
        if (!isset($device)) {
            return null;
        }
        if (!isset($device['hostname'])) {
            return null;
        }

        $url = $this->getApiUrl('/devices/'.$device['hostname'].'/graphs');
        $result = $this->doCurlGet($url);
        if (!$result | !is_array($result)) {
            return null;
        }
        if (!isset($result['graphs'])) {
            return null;
        }

        return $result;
    }





    public function testIsApiValid()
    {
        global $url,$token;

        $api = new LibrenmsApiClient($url, $token);
        $result = $api->isApiValid();
        $this->assertIsBool($result);

        $api = new LibrenmsApiClient('', $token);
        $this->expectException(ApiException::class);
        $api->isApiValid();

        $api = new LibrenmsApiClient($url, '');
        $this->expectExceptionMessage('Unauthenticated');
        $this->expectException(ApiException::class);
        $api->isApiValid();
    }

    public function testGetAlertsStatePass()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);
        $result = $api->getAlerts(0);
        $this->assertIsArray($result);
    }

    public function testGetAlertsStateFail()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);

        $this->expectExceptionMessage(LibrenmsApiClient::EXCEPTION_STATE);
        $this->expectException(ApiException::class);
        $api->getAlerts(3, null, null, null);
    }

    public function testGetAlertsOrderFail()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);

        $this->expectExceptionMessage(LibrenmsApiClient::EXCEPTION_ORDER);
        $this->expectException(ApiException::class);
        $result = $api->getAlerts(0, null, '');
    }

    public function testGetAlertsOrderPass()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);

        $result = $api->getAlerts(0, null, 'asc');
        $this->assertIsArray($result);

        $result = $api->getAlerts(0, null, 'desc');
        $this->assertIsArray($result);
    }

    public function testGetAlertsSeverityFail()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);

        $this->expectExceptionMessage(LibrenmsApiClient::EXCEPTION_SEVERITY);
        $this->expectException(ApiException::class);
        $api->getAlerts(0, 'bad');
    }

    public function testGetAlertsSeverityPass()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);

        $result = $api->getAlerts(null, 'ok');
        if (!isset($result)) {
            $this->assertNull($result);
        } else {
            $this->assertIsArray($result);
        }
    }

    public function testGetAlertDetail()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);

        $result = $api->getAlertDetail(0);
        $this->assertNull($result);
    }

    public function testGetAlertRules()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);

        $result = $api->getAlertRules();
        if (!isset($result)) {
            $this->assertNull($result);
        } else {
            $this->assertIsArray($result);
        }
    }

    public function testGetEndPoints()
    {
        global $url, $token;
        $api = new LibrenmsApiClient($url, $token);

        $result = $api->getEndPoints();
        $this->assertIsArray($result);
    }