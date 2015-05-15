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
 * Class Billing
 * One page checkout status billing block
 */
class Billing extends Form
{
    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '#billing-buttons-container button';

    /**
     * 'Ship to different address' radio button
     *
     * @var string
     */
    protected $useForShipping = '[id="billing:use_for_shipping_no"]';

    /**
     * Wait element
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Field wrapper with label on form.
     *
     * @var string
     */
    protected $fieldLabel = '#billing-new-address-form > .required';

    /**
     * Fill billing address
     *
     * @param Address $billingAddress
     * @param bool $isShippingAddress
     * @return void
     */
    public function fillBilling(
        Address $billingAddress = null,
        $isShippingAddress = false
    ) {
        $this->waitFields();
        if ($billingAddress) {
            $this->fill($billingAddress);
        }

        if ($isShippingAddress) {
            $this->_rootElement->find($this->useForShipping)->click();
        }
        $this->waitForElementNotVisible($this->waitElement);
    }

    /**
     * Click continue on billing information block
     *
     * @return void
     */
    public function clickContinue()
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

    /**
     * Wait for User before fill form which calls JS validation on correspondent fields of form.
     * See details in MAGETWO-31435.
     *
     * @return void
     */
    protected function waitFields()
    {
        /* Wait for field label is visible in the form */
        $this->waitForElementVisible($this->fieldLabel);
    }
}
