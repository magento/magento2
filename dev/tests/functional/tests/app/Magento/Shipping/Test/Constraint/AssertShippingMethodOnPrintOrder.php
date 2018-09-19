<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Page\SalesGuestPrint;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that shipping method was printed correctly on sales guest print page.
 */
class AssertShippingMethodOnPrintOrder extends AbstractConstraint
{
    /**
     * Shipping method and carrier template.
     */
    const SHIPPING_TEMPLATE = "%s - %s";

    /**
     * Assert that shipping method was printed correctly on sales guest print page.
     *
     * @param SalesGuestPrint $salesGuestPrint
     * @param array $shipping
     * @return void
     */
    public function processAssert(SalesGuestPrint $salesGuestPrint, $shipping)
    {
        $expected = sprintf(self::SHIPPING_TEMPLATE, $shipping['shipping_service'], $shipping['shipping_method']);
        \PHPUnit\Framework\Assert::assertTrue(
            $salesGuestPrint->getInfoShipping()->isShippingMethodVisible($expected),
            "Shipping method was printed incorrectly."
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Shipping method was printed correctly.";
    }
}
