<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        if ($this->_address === null) {
            if ($this->isCustomerLoggedIn() || $this->getQuote()->getShippingAddress()) {
                $this->_address = $this->getQuote()->getShippingAddress();
            } else {
                $this->_address = $this->_addressFactory->create();
            }
        }

        return $this->_address;
    }
}
