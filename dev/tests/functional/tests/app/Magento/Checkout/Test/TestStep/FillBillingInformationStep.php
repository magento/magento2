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
 * Class FillBillingInformationStep
 * Fill billing information
 */
class FillBillingInformationStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Address fixture
     *
     * @var Address
     */
    protected $billingAddress;

    /**
     * Checkout method
     *
     * @var string
     */
    protected $checkoutMethod;

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
     * @param Address $billingAddress
     * @param AssertBillingAddressSameAsShippingCheckbox $assertBillingAddressCheckbox,
     * @param string $checkoutMethod
     * @param string $billingCheckboxState
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        $checkoutMethod,
        AssertBillingAddressSameAsShippingCheckbox $assertBillingAddressCheckbox,
        Address $billingAddress = null,
        $billingCheckboxState = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->billingAddress = $billingAddress;
        $this->checkoutMethod = $checkoutMethod;
        $this->assertBillingAddressCheckbox = $assertBillingAddressCheckbox;
        $this->billingCheckboxState = $billingCheckboxState;
    }

    /**
     * Fill billing address
     *
     * @return void
     */
    public function run()
    {
        if ($this->billingCheckboxState !== null) {
            $this->assertBillingAddressCheckbox->processAssert($this->checkoutOnepage, $this->billingCheckboxState);
        }
        $selectedPaymentMethod = $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock();
        $selectedPaymentMethod->getBillingBlock()->fillBilling($this->billingAddress);
    }
}
