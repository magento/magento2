<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\HeaderProvider;

use \Magento\Store\Model\Store;

/**
 * Adds an Content-Security-Policy header to HTTP responses.
 * @since 2.1.0
 */
class UpgradeInsecure extends \Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider
{
    /**
     * Upgrade Insecure Requests Header name
     *
     * @var string
     * @since 2.1.0
     */
    protected $headerName = 'Content-Security-Policy';

    /**
     * Upgrade Insecure Requests header value
     *
     * @var string
     * @since 2.1.0
     */
    protected $headerValue = 'upgrade-insecure-requests';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.1.0
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @since 2.1.0
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function canApply()
    {
        return (bool)$this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_FRONTEND)
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_ADMINHTML)
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_ENABLE_UPGRADE_INSECURE);
    }
}
