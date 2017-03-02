<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\HeaderProvider;

use \Magento\Store\Model\Store;

/**
 * Adds an Content-Security-Policy header to HTTP responses.
 */
class UpgradeInsecure extends \Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider
{
    /**
     * Upgrade Insecure Requests Header name
     *
     * @var string
     */
    protected $headerName = 'Content-Security-Policy';

    /**
     * Upgrade Insecure Requests header value
     *
     * @var string
     */
    protected $headerValue = 'upgrade-insecure-requests';

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
     * {@inheritdoc}
     */
    public function canApply()
    {
        return (bool)$this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_FRONTEND)
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_ADMINHTML)
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_ENABLE_UPGRADE_INSECURE);
    }
}
