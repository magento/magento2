<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Order\Shipment;

use Magento\Mtf\Block\Block;

/**
 * Class Items
 * Items block on shipment view page
 */
class Items extends Block
{
    /**
     * Grand total css selector
     *
     * @var string
     */
    protected $grandTotal = 'td.col.qty';

    /**
     * Get total qty
     *
     * @return int
     */
    public function getTotalQty()
    {
        return trim($this->_rootElement->find($this->grandTotal)->getText());
    }
}
