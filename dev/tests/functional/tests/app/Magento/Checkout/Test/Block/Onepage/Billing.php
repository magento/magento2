<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Block\Form;

/**
 * One page checkout status billing block.
 */
class Billing extends Form
{
    /**
     * Continue checkout button.
     *
     * @var string
     */
    protected $continue = '#billing-buttons-container button';

    /**
     * 'Ship to different address' radio button.
     *
     * @var string
     */
    protected $useForShipping = '[id="billing:use_for_shipping_no"]';

    /**
     * Wait element.
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Fill billing address.
     *
     * @param Address $billingAddress
     * @param bool $isShippingAddress
     * @return void
     */
    public function fillBilling(
        Address $billingAddress = null,
        $isShippingAddress = false
    ) {
        if ($isShippingAddress) {
            $this->_rootElement->find($this->useForShipping)->click();
        }
        if ($billingAddress) {
            $this->fill($billingAddress);
        }
        $this->waitForElementNotVisible($this->waitElement);
    }

    /**
     * Click update on billing information block.
     *
     * @return void
     */
    public function clickUpdate()
    {
        $this->_rootElement->find($this->continue)->click();
        $browser = $this->browser;
        $selector = $this->waitElement;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector);
                return $element->isVisible() == false ? true : null;
            }
        );
    }
}
