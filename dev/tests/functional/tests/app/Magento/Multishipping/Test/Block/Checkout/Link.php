<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Mtf\Block\Block;

/**
 * Multishipping cart link
 */
class Link extends Block
{
    /**
     * Press 'Proceed to Checkout' link
     *
     * @return void
     */
    public function multipleAddressesCheckout()
    {
        $this->_rootElement->click();
    }
}
