<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block;

class CustomerData extends \Magento\Framework\View\Element\Template
{
    /**
     * Get CookieLifeTime
     * @return null|string scopeCode
     */
    public function getCookieLifeTime()
    {
        return $this->_scopeConfig->getValue(
            \Magento\Framework\Session\Config::XML_PATH_COOKIE_LIFETIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
