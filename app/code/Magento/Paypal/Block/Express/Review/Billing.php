<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
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
