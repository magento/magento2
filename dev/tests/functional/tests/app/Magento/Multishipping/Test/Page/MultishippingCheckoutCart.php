<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Page;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * Multishipping checkout cart page.
 */
class MultishippingCheckoutCart extends Page
{
    /**
     * URL for multishipping checkout cart page.
     */
    const MCA = 'multishipping/checkout/cart';

    /**
     * Multishipping cart link block.
     *
     * @var string
     */
    protected $multishippingLinkBlock = '.action.multicheckout';

    /**
     * Get multishipping cart link block.
     *
     * @return \Magento\Multishipping\Test\Block\Checkout\Link
     */
    public function getMultishippingLinkBlock()
    {
        return Factory::getBlockFactory()->getMagentoMultishippingCheckoutLink(
            $this->browser->find($this->multishippingLinkBlock, Locator::SELECTOR_CSS)
        );
    }
}
