<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Url\Plugin;

use \Magento\Store\Model\Store;
use \Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Plugin for \Magento\Framework\Url\SecurityInfo
 */
class SecurityInfo
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
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
