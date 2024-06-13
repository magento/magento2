<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Config\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * System configuration scope
 * @api
 * @since 100.0.2
 */
class ScopeDefiner
{
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_request = $request;
    }

    /**
     * Retrieve current config scope
     *
     * @return string
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
