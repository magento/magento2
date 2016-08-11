<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml sales order create shipping method block.
 */
class Method extends Block
{
    /**
     * 'Get shipping methods and rates' link.
     *
     * @var string
     */
    protected $shippingMethodsLink = '#order-shipping-method-summary a';

    /**
     * Shipping method.
     *
     * @var string
     */
    protected $shippingMethod = '//dt[contains(.,"%s")]/following-sibling::*//*[contains(text(), "%s")]';

    /**
     * Select shipping method.
     *
     * @param array $shippingMethod
     * @return void
     */
    public function selectShippingMethod(array $shippingMethod)
    {
        if ($this->_rootElement->find($this->shippingMethodsLink)->isVisible()) {
            $this->_rootElement->find($this->shippingMethodsLink)->click();
        }
        $selector = sprintf(
            $this->shippingMethod,
            $shippingMethod['shipping_service'],
            $shippingMethod['shipping_method']
        );
        $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->click();
    }
}
