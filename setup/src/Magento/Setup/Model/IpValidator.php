<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

class IpValidator
{
    /**
     * Validates list of ips
     *
     * @param string[] $ips
     * @param bool $noneAllowed
     * @return string[]
     */
    public static function validateIps(array $ips, $noneAllowed)
    {
        $none = [];
        $validIps = [];
        $invalidIps = [];
        $messages = [];
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                $validIps[] = $ip;
            }
            elseif ($ip == 'none') {
                $none[] = $ip;
            } else {
                $invalidIps[] = $ip;
            }
        }

        if (sizeof($none) > 0 && !$noneAllowed) {
            $messages[] = "'none' is not allowed";
        } elseif ($noneAllowed && sizeof($none) > 1) {
            $messages[] = "'none' can be only used once";
        } elseif ($noneAllowed && sizeof($none) > 0 && (sizeof($validIps) > 0 || sizeof($invalidIps) > 0)) {
            $messages[] = "Multiple values are not allowed when 'none' is used";
        } else {
            foreach ($invalidIps as $invalidIp) {
                $messages[] = "Invalid IP $invalidIp";
            }
        }
        return $messages;
    }
}
