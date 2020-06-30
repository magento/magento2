<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\HTTP;

use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Context data for requests
 */
class IpChecker
{
    /**
     * Checks if an IPv4 or IPv6 address is contained in the list of given IPs or subnets.
     *
     * @param string|null $requestIp IP to check
     * @param array $ips List of IPs or subnets
     *
     * @return bool Whether the IP is contained in the list of given IPs or subnets
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function isInRange(?string $requestIp, array $ips): bool
    {
        return IpUtils::checkIp($requestIp, $ips);
    }
}
