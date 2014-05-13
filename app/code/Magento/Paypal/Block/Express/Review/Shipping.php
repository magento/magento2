<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
