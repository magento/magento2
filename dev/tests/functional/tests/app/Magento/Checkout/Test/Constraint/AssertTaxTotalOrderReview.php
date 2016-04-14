<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that total tax price is correct on Order Review page.
 */
class AssertTaxTotalOrderReview extends AbstractConstraint
{
    /**
     * Assert that total tax price is correct on Review page.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $taxTotal
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $taxTotal)
    {
        $reviewTaxTotal = $checkoutOnepage->getReviewBlock()->getTax();

        \PHPUnit_Framework_Assert::assertEquals(
            $reviewTaxTotal,
            number_format($taxTotal, 2),
            "Tax price '$reviewTaxTotal' not equals with price from data set '$taxTotal'."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Tax price equals to price from data set.';
    }
}
