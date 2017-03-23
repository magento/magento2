<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Mtf\Block\Block;

/**
 * Multishipping checkout overview information
 */
class Overview extends Block
{
    /**
     * 'Place Order' button
     *
     * @var string
     */
    protected $placeOrder = '#review-button';

    /**
     * Place order
     *
     * @return void
     */
    public function placeOrder()
    {
        $this->_rootElement->find($this->placeOrder)->click();
    }
}
