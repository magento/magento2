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
            \PHPUnit\Framework\Assert::assertTrue(
                $checkoutOnepage->getShippingMethodBlock()->isLoaderAppeared(),
                'Shipping rate has not been changed.'
            );
        }
        $shippingAvailability = $isShippingAvailable ? 'available' : 'unavailable';
<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertEquals(
=======
        \PHPUnit\Framework\Assert::assertEquals(
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
