<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Block;

/**
 * Class Link
 * One page checkout cart link
 *
 */
class Link extends Block
{
    /**
     * Press 'Proceed to Checkout' link
     *
     * @return void
     */
    public function proceedToCheckout()
    {
        $this->_rootElement->click();
    }
}
