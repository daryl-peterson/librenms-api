<?php

namespace LibrenmsApiClient;

/**
 * LibreNMS API Exception.
 *
 * @category
 *
 * @author      Daryl Peterson <@gmail.com>
 * @copyright   Copyright (c) 2023, Daryl Peterson
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @since       0.0.2
 */
class ApiException extends \Exception
{
    public const ERR_DEVICE_NOT_EXIST = 'Device does not exist.';
    public const ERR_DEVICE_DOES_EXIST = 'Device already exist.';
    public const ERR_INVALID_SNMP = 'Invalid snmp version [1v,v2c,v3].';
    public const ERR_HOSTNAME_IP = 'Missing required Hostname/IP.';
    public const ERR_INVALID_FIELD = 'Invalid device field.';
    public const ERR_LOCATION_EXIST = 'Location already exist.';
    public const ERR_LOCATION_NOT_EXIST = 'Location does not exist';
}
