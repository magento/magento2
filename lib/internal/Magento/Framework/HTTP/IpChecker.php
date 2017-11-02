<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP;

use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Ip checker facade
 */
class IpChecker
{
    /**
     * Checks if an IPv4 or IPv6 address is contained in the list of given IPs or subnets.
     *
     * @param string $requestIp IP to check
     * @param string|array $ips List of IPs or subnets (can be a string if only a single one)
     *
     * @return bool Whether the IP is contained in the list of given IPs or subnets
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function isInRange($requestIp, $ips)
    {
        return IpUtils::checkIp($requestIp, $ips);
    }
}
