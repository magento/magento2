<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Order;

use Magento\Mtf\Client\Locator;

/**
 * Info block on order's view page.
 */
class Info extends \Magento\Sales\Test\Block\Order\Info
{
    /**
     * Shipping method selector.
     *
     * @var string
     */
    protected $shippingMethodSelector = './/.[contains(., "%s")]/..[contains(@class, "box-order-shipping-method")]';

    /**
     * Check if shipping method is visible in print order page.
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isShippingMethodVisible($shippingMethod)
    {
        return $this->_rootElement->find(
            sprintf($this->shippingMethodSelector, $shippingMethod),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }
}
