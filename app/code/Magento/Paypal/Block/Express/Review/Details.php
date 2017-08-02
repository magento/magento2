<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express\Review;

use Magento\Sales\Model\Order\Address;

/**
 * Paypal Express Onepage checkout block
 *
 * @api
 * @since 2.0.0
 */
class Details extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var Address
     * @since 2.0.0
     */
    protected $_address;

    /**
     * Return review shipping address
     *
     * @return Address
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getTotals()
    {
        return $this->getQuote()->getTotals();
    }
}
