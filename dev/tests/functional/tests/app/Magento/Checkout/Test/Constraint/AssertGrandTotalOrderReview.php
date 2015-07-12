<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Order Grand Total is correct on checkoutOnePage.
 */
class AssertGrandTotalOrderReview extends AbstractConstraint
{
    /**
     * Assert that Order Grand Total is correct on checkoutOnePage
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $grandTotal
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $grandTotal)
    {
        $checkoutReviewGrandTotal = $checkoutOnepage->getReviewBlock()->getGrandTotal();

        \PHPUnit_Framework_Assert::assertEquals(
            $checkoutReviewGrandTotal,
            number_format($grandTotal, 2),
            "Grand Total price: $checkoutReviewGrandTotal not equals with price from data set: " . $grandTotal
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Grand Total price equals to price from data set.';
    }
}
