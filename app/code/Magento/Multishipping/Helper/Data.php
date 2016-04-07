<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Helper;

/**
 * Data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+
     * Xml paths for multishipping checkout
     **/
    const XML_PATH_CHECKOUT_MULTIPLE_AVAILABLE = 'multishipping/options/checkout_multiple';

    const XML_PATH_CHECKOUT_MULTIPLE_MAXIMUM_QUANTITY = 'multishipping/options/checkout_multiple_maximum_qty';

    /**#@-*/

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * Retrieve checkout quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * Get maximum quantity allowed for shipping to multiple addresses
     *
     * @return int
     */
    public function getMaximumQty()
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_CHECKOUT_MULTIPLE_MAXIMUM_QUANTITY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if multishipping checkout is available
     * There should be a valid quote in checkout session. If not, only the config value will be returned
     *
     * @return bool
     */
    public function isMultishippingCheckoutAvailable()
    {
        $quote = $this->getQuote();
        $isMultiShipping = $this->scopeConfig->isSetFlag(self::XML_PATH_CHECKOUT_MULTIPLE_AVAILABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$quote || !$quote->hasItems()) {
            return $isMultiShipping;
        }
        return $isMultiShipping && !$quote->hasItemsWithDecimalQty() && $quote->validateMinimumAmount(
            true
        ) &&
            $quote->getItemsSummaryQty() - $quote->getItemVirtualQty() > 0 &&
            $quote->getItemsSummaryQty() <= $this->getMaximumQty();
    }
}
