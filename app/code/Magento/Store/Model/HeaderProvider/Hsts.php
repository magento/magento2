<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\HeaderProvider;

use \Magento\Store\Model\Store;

/**
 * Adds an Strict-Transport-Security (HSTS) header to HTTP responses.
 */
class Hsts extends \Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider
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
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_ENABLE_HSTS);
    }
}
