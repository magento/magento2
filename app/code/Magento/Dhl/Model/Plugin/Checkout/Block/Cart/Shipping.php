<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Dhl\Model\Plugin\Checkout\Block\Cart;

/**
 * Checkout cart shipping block plugin
 */
class Shipping
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\LayoutProcessor $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsStateActive(\Magento\Checkout\Block\Cart\LayoutProcessor $subject, $result)
    {
        return (bool)$result || (bool)$this->_scopeConfig->getValue(
            'carriers/dhl/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Checkout\Block\Cart\LayoutProcessor $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsCityActive(\Magento\Checkout\Block\Cart\LayoutProcessor $subject, $result)
    {
        return (bool)$result || (bool)$this->_scopeConfig->getValue(
            'carriers/dhl/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
