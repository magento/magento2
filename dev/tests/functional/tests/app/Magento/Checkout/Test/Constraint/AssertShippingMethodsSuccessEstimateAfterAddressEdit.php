<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\TestStep\FillShippingAddressStep;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Asserts that shipping methods are present on checkout page after address modification.
 */
class AssertShippingMethodsSuccessEstimateAfterAddressEdit extends AssertShippingMethodsEstimate
{
    /**
     * Asserts that shipping methods are present on checkout page after address modification.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param TestStepFactory $testStepFactory
     * @param FixtureFactory $fixtureFactory
     * @param BrowserInterface $browser
     * @param array $editAddressData
     * @return void
     */
    public function processAssert(
        CheckoutOnepage $checkoutOnepage,
        TestStepFactory $testStepFactory,
        FixtureFactory $fixtureFactory,
        BrowserInterface $browser,
        array $editAddressData = []
    ) {
        if (!empty ($editAddressData)) {
            $address = $fixtureFactory->createByCode('address', ['data' => $editAddressData]);
            $testStepFactory->create(
                FillShippingAddressStep::class,
                [
                    'checkoutOnepage' => $checkoutOnepage,
                    'shippingAddress' => $address,
                ]
            )->run();

            $this->assert($checkoutOnepage, $browser);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Shipping methods are present on checkout page after address modification.";
    }
}
