<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Multishipping\Test\Fixture\GuestPaypalDirect;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class Overview
 * Multishipping checkout overview information
 *
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
     * @param GuestPaypalDirect $fixture
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function placeOrder(GuestPaypalDirect $fixture = null)
    {
        $this->_rootElement->find($this->placeOrder, Locator::SELECTOR_CSS)->click();
    }
}
