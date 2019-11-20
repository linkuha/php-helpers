<?php
/**
 * Created by PhpStorm.
 * User: linkuha (Pudich Aleksandr)
 * Date: 30.08.2019
 * Time: 19:10
 */

namespace SimpleLibs;


class IpHelper
{
    public static $cidr = ['0.0.0.0', '128.0.0.0', '192.0.0.0', '224.0.0.0',
        '240.0.0.0', '248.0.0.0', '252.0.0.0', '254.0.0.0', '255.0.0.0',
        '255.128.0.0', '255.192.0.0', '255.224.0.0', '255.240.0.0',
        '255.248.0.0', '255.252.0.0', '255.254.0.0', '255.255.0.0',
        '255.255.128.0', '255.255.192.0', '255.255.224.0', '255.255.240.0',
        '255.255.248.0', '255.255.252.0', '255.255.254.0', '255.255.255.0',
        '255.255.255.128', '255.255.255.192', '255.255.255.224',
        '255.255.255.240', '255.255.255.248', '255.255.255.252',
        '255.255.255.254', '255.255.255.255'
    ];

    /**
     * For example, arabic sub-nets (prefixes) of one provider
     *     you can see here http://ipv4info.com/as-info/s88081f/AS30873.html
     *
     * @param $ip
     * @param array $subNets Example of subnet: '5.100.160.0/21', '5.255.0.0/21'
     * @return bool
     */
    public static function inSubNets($ip, array $subNets)
    {
        $ipDecimal = intval(sprintf("%u", ip2long($ip)));
        $flag = false;
        foreach ($subNets as $subnet) {
            $line = explode('/', $subnet);
            $net = intval(sprintf("%u", ip2long($line[0])));
            if (isset(self::$cidr[$line[1]])) {
                $mask = self::$cidr[$line[1]];
            } else {
                $mask = long2ip(pow(2, 32) - pow(2, (32 - $line[1])));
            }
            $mask = intval(sprintf("%u", ip2long($mask)));
            if (($ipDecimal & $mask) == $net) {
                $flag = true;
                break;
            }
        }
        return $flag;
    }
}
