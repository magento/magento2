<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Shipping\Test\Block\Order;

use Mtf\Client\Element\Locator;

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
