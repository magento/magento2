<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Constraint\Sandbox;

use Magento\Paypal\Test\Page\Sandbox\ExpressReview;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Order total is correct on PayPal Review page.
 */
class AssertTotalPaypalReview extends AbstractConstraint
{
    /**
     * Assert that Order Grand Total is correct on PayPal page.
     *
     * @param ExpressReview $expressReview
     * @param string $total
     * @return void
     */
    public function processAssert(ExpressReview $expressReview, $total)
    {
        $reviewTotal = $expressReview->getExpressMainReviewBlock()->getReviewBlock()->getTotal();

        \PHPUnit\Framework\Assert::assertEquals(
            $reviewTotal,
            number_format($total, 2),
            'Total price: \'' . $reviewTotal
            . '\' not equals with price from data set: \'' . $total . '\''
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Total price equals to price from data set.';
    }
}
