<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Paypal Express Onepage checkout block for Billing Address
 */
namespace Magento\Paypal\Block\Express\Review;

class Billing extends \Magento\Checkout\Block\Onepage\Billing
{
    /**
     * Return Sales Quote Address model
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getAddress()
    {
        if ($this->_address === null) {
            if ($this->isCustomerLoggedIn() || $this->getQuote()->getBillingAddress()) {
                $this->_address = $this->getQuote()->getBillingAddress();
                if (!$this->_address->getFirstname()) {
                    $this->_address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
                }
                if (!$this->_address->getLastname()) {
                    $this->_address->setLastname($this->getQuote()->getCustomer()->getLastname());
                }
            } else {
                $this->_address = $this->_addressFactory->create();
            }
        }

        return $this->_address;
    }
}
