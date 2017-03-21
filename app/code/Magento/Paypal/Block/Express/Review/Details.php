<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express\Review;

use Magento\Sales\Model\Order\Address;

/**
 * Paypal Express Onepage checkout block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Details extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var Address
     */
    protected $_address;

    /**
     * Return review shipping address
     *
     * @return Address
     */
    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }

    /**
     * Return review quote totals
     *
     * @return array
     */
    public function getTotals()
    {
        return $this->getQuote()->getTotals();
    }
}
