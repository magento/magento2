<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Order Subtotal is correct on Order Review page.
 */
class AssertSubTotalOrderReview extends AbstractConstraint
{
    /**
     * Assert that Order Subtotal is correct on Review page.
     *
     * @param CheckoutOnepage $checkoutOnepage
     * @param string $subTotal
     * @return void
     */
    public function processAssert(CheckoutOnepage $checkoutOnepage, $subTotal)
    {
        $reviewSubTotal = $checkoutOnepage->getReviewBlock()->getSubtotal();

        \PHPUnit\Framework\Assert::assertEquals(
            $reviewSubTotal,
            number_format($subTotal, 2),
            'Subtotal price: \'' . $reviewSubTotal
            . '\' not equals with price from data set: \'' . $reviewSubTotal . '\''
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Subtotal price equals to price from data set.';
    }
}
