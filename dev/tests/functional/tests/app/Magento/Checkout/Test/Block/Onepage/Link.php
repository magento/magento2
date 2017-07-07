<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    /**
     * Get title of Proceed to Checkout link
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_rootElement->getText();
    }
}
