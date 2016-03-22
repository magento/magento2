<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage\Payment\Method;

use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * One page checkout status billing block.
 */
class Billing extends Form
{
    /**
     * Update billing address button.
     *
     * @var string
     */
    protected $updateButtonSelector = '.action.action-update';

    /**
     * Wait element.
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * "Same as Shipping" checkbox selector.
     *
     * @var string
     */
    protected $sameAsShippingCheckbox = '[name="billing-address-same-as-shipping"]';

    /**
     * Fill billing address.
     *
     * @param Address $billingAddress
     * @return void
     */
    public function fillBilling(Address $billingAddress)
    {
        $this->fill($billingAddress);
        $this->clickUpdate();
        $this->waitForElementNotVisible($this->waitElement);
    }

    /**
     * Click update on billing information block.
     *
     * @return void
     */
    public function clickUpdate()
    {
        $this->_rootElement->find($this->updateButtonSelector)->click();
    }

    /**
     * Get "Same as shipping" checkbox value.
     *
     * @return string
     */
    public function getSameAsShippingCheckboxValue()
    {
        return $this->_rootElement->find($this->sameAsShippingCheckbox, Locator::SELECTOR_CSS, 'checkbox')->getValue();
    }
}
