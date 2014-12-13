<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderTotalOnReviewPage
 * Assert that Order Grand Total is correct on checkoutOnePage
 */
class AssertOrderTotalOnReviewPage extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

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
            $grandTotal,
            'Grand Total price: \'' . $checkoutReviewGrandTotal
            . '\' not equals with price from data set: \'' . $grandTotal . '\''
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
