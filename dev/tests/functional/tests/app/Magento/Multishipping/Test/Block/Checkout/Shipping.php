<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Block\Checkout;

use Magento\Multishipping\Test\Fixture\GuestPaypalDirect;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Multishipping checkout shipping.
 */
class Shipping extends Block
{
    /**
     * 'Continue' button.
     *
     * @var string
     */
    protected $continueButton = '.action.continue';

    /**
     * Select shipping methods.
     *
     * @param array $shippingMethods
     * @return void
     */
    public function selectShippingMethod($shippingMethods)
    {
        $count = 1;
        foreach ($shippingMethods as $shipping) {
            if ($shipping instanceof \Magento\Shipping\Test\Fixture\Method) {
                $method = $shipping->getData('fields');
                $shippingService = $method['shipping_service'];
                $shippingMethod = $method['shipping_method'];
            } else {
                $shippingService = $shipping['shipping_service'];
                $shippingMethod = $shipping['shipping_method'];
            }
            $selector = '//div[' . $count++ . '][contains(@class,"block-shipping")]//dt[text()="'
                . $shippingService . '"]/following-sibling::*//*[contains(text(), "'
                . $shippingMethod . '")]';
            $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->click();
        }
        $this->clickContinueButton();
    }

    /**
     * Click continue button.
     *
     * @return void
     */
    public function clickContinueButton()
    {
        $this->_rootElement->find($this->continueButton)->click();
    }
}
