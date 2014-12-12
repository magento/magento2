<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Paypal Express Onepage checkout block for Shipping Address
 */
namespace Magento\Paypal\Block\Express\Review;

class Shipping extends \Magento\Checkout\Block\Onepage\Shipping
{
    /**
     * Return Sales Quote Address model (shipping address)
     *
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            if ($this->isCustomerLoggedIn() || $this->getQuote()->getShippingAddress()) {
                $this->_address = $this->getQuote()->getShippingAddress();
            } else {
                $this->_address = $this->_addressFactory->create();
            }
        }

        return $this->_address;
    }
}
