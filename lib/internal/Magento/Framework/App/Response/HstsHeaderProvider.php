<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Response;

use \Magento\Store\Model\Store;

/**
 * Adds an Strict-Transport-Security (HSTS) header to HTTP responses.
 */
class HstsHeaderProvider implements \Magento\Framework\App\Response\HeaderProviderInterface
{
    /**
     * Enable HSTS config path
     */
    const XML_PATH_ENABLE_HSTS = 'web/secure/enable_hsts';

    /** Strict-Transport-Security (HSTS) Header name */
    const HEADER_NAME = 'Strict-Transport-Security';

    /**
     * Strict-Transport-Security (HSTS) header value
     */
    const HEADER_VALUE = 'max-age=31536000';

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
     * Get header name
     *
     * @return string
     */
    public function getName()
    {
        return $this::HEADER_NAME;
    }

    /**
     * Get header value
     *
     * @return string
     */
    public function getValue()
    {
        return $this::HEADER_VALUE;
    }

    public function canApply()
    {
        return (bool)$this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_FRONTEND)
            && $this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_ADMINHTML)
            && $this->scopeConfig->isSetFlag($this::XML_PATH_ENABLE_HSTS);
    }
}
