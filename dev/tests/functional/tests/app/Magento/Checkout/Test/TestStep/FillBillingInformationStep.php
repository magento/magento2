<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Checkout\Test\Constraint\AssertBillingAddressSameAsShippingCheckbox;

/**
 * Fill billing information.
 */
class FillBillingInformationStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Address fixture.
     *
     * @var Address
     */
    protected $billingAddress;

    /**
     * "Same as Shipping" checkbox value assertion.
     *
     * @var AssertBillingAddressSameAsShippingCheckbox
     */
    protected $assertBillingAddressCheckbox;

    /**
     * "Same as Shipping" checkbox expected value.
     *
     * @var string
     */
    protected $billingCheckboxState;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param AssertBillingAddressSameAsShippingCheckbox $assertBillingAddressCheckbox
     * @param Address $billingAddress
     * @param string $billingCheckboxState
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertBillingAddressSameAsShippingCheckbox $assertBillingAddressCheckbox,
        Address $billingAddress = null,
        $billingCheckboxState = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->billingAddress = $billingAddress;
        $this->assertBillingAddressCheckbox = $assertBillingAddressCheckbox;
        $this->billingCheckboxState = $billingCheckboxState;
    }

    /**
     * Fill billing address.
     *
     * @return void
     */
    public function run()
    {
        if ($this->billingCheckboxState) {
            $this->assertBillingAddressCheckbox->processAssert($this->checkoutOnepage, $this->billingCheckboxState);
        }

        if ($this->billingAddress) {
            $selectedPaymentMethod = $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock();
            $selectedPaymentMethod->getBillingBlock()->fillBilling($this->billingAddress);
        }
    }
}
