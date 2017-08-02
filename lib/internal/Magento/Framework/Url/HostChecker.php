<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

use Magento\Framework\UrlInterface;

/**
 * Class provides functionality for checks of a host name
 * @since 2.2.0
 */
class HostChecker
{
    /**
     * @var \Magento\Framework\Url\ScopeResolverInterface
     * @since 2.2.0
     */
    private $scopeResolver;

    /**
     * @param ScopeResolverInterface $scopeResolver
     * @since 2.2.0
     */
    public function __construct(ScopeResolverInterface $scopeResolver)
    {
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Check if provided URL is one of the domain URLs assigned to scopes
     *
     * @param string $url
     * @return bool
     * @since 2.2.0
     */
    public function isOwnOrigin($url)
    {
        $scopeHostNames = [];
        $hostName = parse_url($url, PHP_URL_HOST);
        if (empty($hostName)) {
            return true;
        }
        foreach ($this->scopeResolver->getScopes() as $scope) {
            $scopeHostNames[] = parse_url($scope->getBaseUrl(), PHP_URL_HOST);
            $scopeHostNames[] = parse_url($scope->getBaseUrl(UrlInterface::URL_TYPE_LINK, true), PHP_URL_HOST);
        }
        $scopeHostNames = array_unique($scopeHostNames);
        return in_array($hostName, $scopeHostNames);
    }
}
