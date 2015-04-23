<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Checkout\Block\Cart;

use Magento\Framework\View\Block\IdentityInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Cart sidebar block
 */
class Sidebar extends AbstractCart
{
    /**
     * Xml pah to checkout sidebar count value
     */
    const XML_PATH_CHECKOUT_SIDEBAR_DISPLAY = 'checkout/sidebar/display';

    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = false;
        $this->_taxConfig = $taxConfig;
        $this->jsLayout = isset($data['jsLayout']) ? $data['jsLayout'] : [];
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * Get one page checkout page url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout/onepage');
    }

    /**
     * Get shoppinc cart page url
     *
     * @return string
     */
    public function getShoppingCartUrl()
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * Return whether subtotal should be displayed including tax
     * TODO: dependence on taxConfig
     * @return int
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisplaySubtotalInclTax()
    {
        return (int)$this->_taxConfig->displayCartSubtotalInclTax();
    }

    /**
     * Return whether subtotal should be displayed excluding tax
     * TODO: dependence on taxConfig
     * @return int
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getDisplaySubtotalExclTax()
    {
        return (int)$this->_taxConfig->displayCartSubtotalExclTax();
    }

    /**
     * Get update cart item url
     *
     * @return string
     */
    public function getUpdateItemQtyUrl()
    {
        return $this->getUrl('checkout/sidebar/updateItemQty');
    }

    /**
     * Get remove cart item url
     *
     * @return string
     */
    public function getRemoveItemUrl()
    {
        return $this->getUrl('checkout/sidebar/removeItem');
    }

    /**
     * Define if Mini Shopping Cart Pop-Up Menu enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsNeedToDisplaySideBar()
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_CHECKOUT_SIDEBAR_DISPLAY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return totals from custom quote if needed
     *
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            $quote = $this->getCustomQuote() ? $this->getCustomQuote() : $this->getQuote();
            $this->_totals = $quote->getTotals();
        }
        return $this->_totals;
    }

    /**
     * Retrieve subtotal block html
     *
     * @return string
     */
    public function getTotalsHtml()
    {
        return $this->getLayout()->getBlock('checkout.cart.minicart.totals')->toHtml();
    }
}
