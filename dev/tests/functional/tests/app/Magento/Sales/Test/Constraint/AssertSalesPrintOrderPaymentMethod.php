<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\SalesGuestPrint;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that payment method was printed correctly on sales guest print page.
 */
class AssertSalesPrintOrderPaymentMethod extends AbstractConstraint
{
    /**
     * Assert that payment method was printed correctly on sales guest print page.
     *
     * @param SalesGuestPrint $salesGuestPrint
     * @param string $paymentMethod
     * @return void
     */
    public function processAssert(SalesGuestPrint $salesGuestPrint, $paymentMethod)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $salesGuestPrint->getInfoBlock()->isPaymentMethodVisible($paymentMethod),
            "Payment method was printed incorrect on sales guest print page."
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Payment method was printed correctly.";
    }
}
