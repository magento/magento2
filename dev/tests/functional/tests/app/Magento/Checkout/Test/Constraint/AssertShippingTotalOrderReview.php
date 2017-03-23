<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that shipping price is correct on Order Review page.
 */
class AssertShippingTotalOrderReview extends AbstractConstraint
{
    /**
     * Assert that shipping price is correct on Review page.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $shippingTotal
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $shippingTotal)
    {
        $reviewShippingTotal = $checkoutOnepage->getReviewBlock()->getShippingExclTax();

        \PHPUnit_Framework_Assert::assertEquals(
            $reviewShippingTotal,
            number_format($shippingTotal, 2),
            'Shipping price: \'' . $reviewShippingTotal
            . '\' not equals with price from data set: \'' . $shippingTotal . '\''
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shipping price equals to price from data set.';
    }
}
