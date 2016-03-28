<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

use Magento\Sales\Test\Page\SalesGuestPrint;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that sales rule amount printed correctly on sales guest print page.
 */
class AssertSalesRuleOnPrintOrder extends AbstractConstraint
{
    /**
     * Assert that sales rule amount printed correctly on sales guest print page.
     *
     * @param SalesGuestPrint $salesGuestPrint
     * @param array $prices
     * @return void
     */
    public function processAssert(SalesGuestPrint $salesGuestPrint, array $prices)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            abs($prices['discount']),
            $salesGuestPrint->getViewSalesRule()->getItemBlock()->getSalesRuleDiscount(),
            "Sales rule amount not equals."
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Sales rule amount was printed correctly.";
    }
}
