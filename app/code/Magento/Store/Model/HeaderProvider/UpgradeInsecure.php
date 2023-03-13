<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\HeaderProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider;
use Magento\Store\Model\Store;

/**
 * Adds an Content-Security-Policy header to HTTP responses.
 */
class UpgradeInsecure extends AbstractHeaderProvider
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
     * UpgradeInsecure constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @inheritdoc
     */
    public function canApply()
    {
        return $this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_FRONTEND)
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_ADMINHTML)
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_ENABLE_UPGRADE_INSECURE);
    }
}
