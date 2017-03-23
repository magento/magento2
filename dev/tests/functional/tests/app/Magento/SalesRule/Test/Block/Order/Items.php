<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Order;

/**
 * Items block on order's view page.
 */
class Items extends \Magento\Sales\Test\Block\Order\Items
{
    /**
     * Sales Rule selector.
     *
     * @var string
     */
    protected $salesRuleSelector = '.discount > .amount > span.price';

    /**
     * Get sales rule discount.
     *
     * @return string
     */
    public function getSalesRuleDiscount()
    {
        return $this->escapeCurrency($this->_rootElement->find($this->salesRuleSelector)->getText());
    }
}
