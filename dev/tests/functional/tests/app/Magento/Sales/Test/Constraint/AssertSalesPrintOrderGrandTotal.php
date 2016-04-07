<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\SalesGuestPrint;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Grand Total price was printed correctly on sales guest print page.
 */
class AssertSalesPrintOrderGrandTotal extends AbstractConstraint
{
    /**
     * Assert that Grand Total price was printed correctly on sales guest print page.
     *
     * @param SalesGuestPrint $salesGuestPrint
     * @param array $prices
     * @return void
     */
    public function processAssert(SalesGuestPrint $salesGuestPrint, array $prices)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            number_format(array_sum($prices), 2),
            $salesGuestPrint->getViewBlock()->getItemBlock()->getGrandTotal(),
            "Grand total was printed incorrectly."
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Grand total was printed correctly.";
    }
}
