<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab;

use Magento\Mtf\Block\Block;

/**
 * Order information tab block.
 */
class Info extends Block
{
    /**
     * Order status selector
     *
     * @var string
     */
    protected $orderStatus = '#order_status';

    /**
     * Get order status from info block
     *
     * @return array|string
     */
    public function getOrderStatus()
    {
        return $this->_rootElement->find($this->orderStatus)->getText();
    }
}
