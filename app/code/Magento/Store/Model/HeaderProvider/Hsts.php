<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\HeaderProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider;
use Magento\Store\Model\Store;

/**
 * Adds an Strict-Transport-Security (HSTS) header to HTTP responses.
 */
class Hsts extends AbstractHeaderProvider
{
    /**
     * Strict-Transport-Security (HSTS) Header name
     *
     * @var string
     */
    protected $headerName = 'Strict-Transport-Security';

    /**
     * Strict-Transport-Security (HSTS) header value
     *
     * @var string
     */
    protected $headerValue = 'max-age=31536000';

    /**
     * Hsts constructor.
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
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_ENABLE_HSTS);
    }
}
