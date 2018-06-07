<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that Shipping rate changes due to City change.
 */
class AssertCityBasedShippingRateChanged extends AbstractConstraint
{
    /**
     * Assert that Shipping rate changed on City change.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $shippingMethod
     * @param bool $isShippingAvailable
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $shippingMethod, $isShippingAvailable)
    {
        if ($isShippingAvailable) {
            \PHPUnit_Framework_Assert::assertTrue(
                $checkoutOnepage->getShippingMethodBlock()->isLoaderAppeared(),
                'Shipping rate has not been changed.'
            );
        }
        $shippingAvailability = $isShippingAvailable ? 'available' : 'unavailable';
        \PHPUnit_Framework_Assert::assertEquals(
            $isShippingAvailable,
            $checkoutOnepage->getShippingMethodBlock()->isShippingMethodAvailable($shippingMethod),
            "Shipping rates for {$shippingMethod['shipping_service']} should be $shippingAvailability."
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Shipping rate has been changed.";
    }
}
