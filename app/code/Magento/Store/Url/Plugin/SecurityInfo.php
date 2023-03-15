<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Url\Plugin;

use Closure;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Url\SecurityInfo as UrlSecurityInfo;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use Magento\Store\Model\Store;

/**
 * Plugin for \Magento\Framework\Url\SecurityInfo
 */
class SecurityInfo
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check if secure URLs are enabled.
     *
     * @param UrlSecurityInfo $subject
     * @param callable $proceed
     * @param string $url
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsSecure(UrlSecurityInfo $subject, Closure $proceed, $url)
    {
        if ($this->scopeConfig->getValue(Store::XML_PATH_SECURE_IN_FRONTEND, StoreScopeInterface::SCOPE_STORE)) {
            return $proceed($url);
        }

        return false;
    }
}
