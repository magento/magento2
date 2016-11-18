<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that Shipping rate changes due to City change.
 */
class AssertCityBasedShippingRateChanged extends AbstractConstraint
{
    /**
     * Wait element.
     *
     * @var string
     */
    private $waitElement = '.loading-mask';

    /**
     * Onepage Checkout page.
     *
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * Assert that Shipping rate changed on City change.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $rateChanged = $this->waitLoader();

        \PHPUnit_Framework_Assert::assertTrue(
            $rateChanged,
            'Shipping rate has not been changed.'
        );
    }

    /**
     * Wait load block.
     *
     * @return bool
     */
    protected function waitLoader()
    {
        return $this->checkoutOnepage->getShippingBlock()->waitForElementVisible($this->waitElement);
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
