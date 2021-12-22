<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Cart form view model.
 */
class Cart implements ArgumentInterface
{
    /**
     * Config settings path to enable clear shopping cart button
     */
    private const XPATH_CONFIG_ENABLE_CLEAR_SHOPPING_CART = 'checkout/cart/enable_clear_shopping_cart';

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
    }

    /**
     * Check if clear shopping cart button is enabled
     *
     * @return bool
     */
    public function isClearShoppingCartEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XPATH_CONFIG_ENABLE_CLEAR_SHOPPING_CART,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
