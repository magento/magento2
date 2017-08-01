<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * System configuration scope
 * @api
 * @since 2.0.0
 */
class ScopeDefiner
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $_request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_request = $request;
    }

    /**
     * Retrieve current config scope
     *
     * @return string
     * @since 2.0.0
     */
    public function getScope()
    {
        return $this->_request->getParam(
            'store'
        ) ? StoreScopeInterface::SCOPE_STORE : ($this->_request->getParam(
            'website'
        ) ? StoreScopeInterface::SCOPE_WEBSITE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }
}
