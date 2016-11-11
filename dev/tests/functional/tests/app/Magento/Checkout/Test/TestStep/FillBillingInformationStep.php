<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Constraint\AssertBillingAddressSameAsShippingCheckbox;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\TestStep\TestStepInterface;

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
     * Billing Address fixture.
     *
     * @var Address
     */
    protected $billingAddress;

    /**
     * Shipping Address fixture.
     *
     * @var Address
     */
    protected $shippingAddress;

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
     * @param Address $shippingAddress
     * @param string $billingCheckboxState
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AssertBillingAddressSameAsShippingCheckbox $assertBillingAddressCheckbox,
        Address $billingAddress = null,
        Address $shippingAddress = null,
        $billingCheckboxState = null
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->billingAddress = $billingAddress;
        $this->shippingAddress = $shippingAddress;
        $this->assertBillingAddressCheckbox = $assertBillingAddressCheckbox;
        $this->billingCheckboxState = $billingCheckboxState;
    }

    /**
     * Fill billing address.
     *
     * @return array
     */
    public function run()
    {
        if ($this->billingCheckboxState) {
            $this->assertBillingAddressCheckbox->processAssert($this->checkoutOnepage, $this->billingCheckboxState);
        }

        if ($this->billingAddress) {
            $selectedPaymentMethod = $this->checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock();
            if ($this->shippingAddress) {
                $selectedPaymentMethod->getBillingBlock()->unsetSameAsShippingCheckboxValue();
            }
            $selectedPaymentMethod->getBillingBlock()->fillBilling($this->billingAddress);
        }
        return [
            'billingAddress' => $this->billingAddress
        ];
    }
}
