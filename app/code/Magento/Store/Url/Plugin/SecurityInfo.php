<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Url\Plugin;

use \Magento\Store\Model\Store;
use \Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Plugin for \Magento\Framework\Url\SecurityInfo
 * @since 2.0.0
 */
class SecurityInfo
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if secure URLs are enabled.
     *
     * @param \Magento\Framework\Url\SecurityInfo $subject
     * @param callable $proceed
     * @param string $url
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function aroundIsSecure(\Magento\Framework\Url\SecurityInfo $subject, \Closure $proceed, $url)
    {
        if ($this->scopeConfig->getValue(Store::XML_PATH_SECURE_IN_FRONTEND, StoreScopeInterface::SCOPE_STORE)) {
            return $proceed($url);
        } else {
            return false;
        }
    }
}
