<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Mtf\Block\Block;

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
