<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\SalesGuestPrint;
use Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Grand Total price was printed correctly on sales guest print page.
 */
class AssertSalesPrintOrderGrandTotal extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that Grand Total price was printed correctly on sales guest print page.
     *
     * @param SalesGuestPrint $salesGuestPrint
     * @param string $grandTotal
     * @return void
     */
    public function processAssert(SalesGuestPrint $salesGuestPrint, $grandTotal)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $grandTotal,
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
