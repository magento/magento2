<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * New address select selector.
     *
     * @var string
     */
    private $newAddressSelect = '[name="billing_address_id"]';

    /**
     * New address option value.
     *
     * @var string
     */
    private $newAddressOption = 'New Address';

    /**
     * Fill billing address.
     *
     * @param Address $billingAddress
     * @return void
     */
    public function fillBilling(Address $billingAddress)
    {
        $select = $this->_rootElement->find($this->newAddressSelect, Locator::SELECTOR_CSS, 'select');
        if ($select && $select->isVisible()) {
            $select->setValue($this->newAddressOption);
        }
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

    /**
     * Unset "Same as shipping" checkbox value.
     *
     * @return void
     */
    public function unsetSameAsShippingCheckboxValue()
    {
        $this->_rootElement->find($this->sameAsShippingCheckbox, Locator::SELECTOR_CSS, 'checkbox')->setValue('No');
    }
}
