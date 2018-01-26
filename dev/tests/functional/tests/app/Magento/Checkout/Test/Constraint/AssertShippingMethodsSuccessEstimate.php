<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Asserts that shipping methods are present on checkout page.
 */
class AssertShippingMethodsSuccessEstimate extends AssertShippingMethodsEstimate
{
    /**
     * Asserts that shipping methods are present on checkout page.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, BrowserInterface $browser)
    {
        $this->assert($checkoutOnepage, $browser);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Shipping methods are present on checkout page.";
    }
}
