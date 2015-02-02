<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Page;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * class MultishippingCheckoutCart
 *
 */
class MultishippingCheckoutCart extends Page
{
    /**
     * URL for multishipping checkout cart page
     */
    const MCA = 'multishipping/checkout/cart';

    /**
     * Multishipping cart link block
     *
     * @var string
     */
    protected $multishippingLinkBlock = '.action.multicheckout';

    /**
     * Get multishipping cart link block
     *
     * @return \Magento\Multishipping\Test\Block\Checkout\Link
     */
    public function getMultishippingLinkBlock()
    {
        return Factory::getBlockFactory()->getMagentoMultishippingCheckoutLink(
            $this->_browser->find($this->multishippingLinkBlock, Locator::SELECTOR_CSS)
        );
    }
}
