<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Checkout cart shipping block plugin
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\OfflineShipping\Model\Plugin\Checkout\Block\Cart;

/**
 * Class \Magento\OfflineShipping\Model\Plugin\Checkout\Block\Cart\Shipping
 *
 * @since 2.0.0
 */
class Shipping
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\LayoutProcessor $subject
     * @param  bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterIsStateActive(\Magento\Checkout\Block\Cart\LayoutProcessor $subject, $result)
    {
        return (bool)$result || (bool)$this->_scopeConfig->getValue(
            'carriers/tablerate/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
