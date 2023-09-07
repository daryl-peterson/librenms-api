<?php

namespace LibrenmsApiClient;

/**
 * Class description.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2020, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       1.0.0
 */
class DeviceValidator implements ValidatorInterface
{
    public function validate(array &$device)
    {
        if (!isset($device['hostname'])) {
            throw new ApiException(ApiException::ERR_HOSTNAME_IP);
        }

        $this->snmp($device);
        $this->pingOnly($device);
    }

    private function snmp(array $device)
    {
        $snmpVersions = ['v1', 'v2c', 'v3'];

        if (isset($device['snmpver'])) {
            $ver = $device['snmpver'];

            if (!in_array($ver, $snmpVersions)) {
                throw new ApiException(ApiException::ERR_INVALID_SNMP);
            }
        }
    }

    private function pingOnly(array &$device)
    {
        $icmpOnly = [
            'port',
            'transport',
            'snmpver',
            'community',
            'authlevel',
            'authname',
            'authpass',
            'authalgo',
            'cryptopass',
            'cryptoalgo',
            'port_association_mode',
        ];

        if (isset($device['snmp_disable']) && $device['snmp_disable']) {
            foreach ($icmpOnly as $keyName) {
                unset($device[$keyName]);
            }
        }
    }
}
