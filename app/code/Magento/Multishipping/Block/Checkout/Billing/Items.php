<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Mustishipping checkout shipping
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Multishipping\Block\Checkout\Billing;

/**
 * @api
 * @since 2.0.0
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @since 2.0.0
     */
    protected $_multishipping;

    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->_multishipping = $multishipping;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get multishipping checkout model
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     * @since 2.0.0
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Retrieve virtual product edit url
     *
     * @return string
     * @since 2.0.0
     */
    public function getVirtualProductEditUrl()
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * Retrieve virtual product collection array
     *
     * @return array
     * @since 2.0.0
     */
    public function getVirtualQuoteItems()
    {
        $items = [];
        foreach ($this->getQuote()->getItemsCollection() as $_item) {
            if ($_item->getProduct()->getIsVirtual() && !$_item->getParentItemId()) {
                $items[] = $_item;
            }
        }
        return $items;
    }
}
