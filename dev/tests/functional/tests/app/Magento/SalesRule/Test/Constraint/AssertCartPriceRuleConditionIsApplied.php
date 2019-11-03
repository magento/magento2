<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

/**
 * Assert that Cart Price Rule is applied in Shopping Cart.
 */
class AssertCartPriceRuleConditionIsApplied extends AssertCartPriceRuleApplying
{
    /**
     * Assert that Cart Price Rule is applied in Shopping Cart.
     *
     * @return void
     */
    protected function assert()
    {
        $this->checkoutCart->getTotalsBlock()->waitForShippingPriceBlock();
        $this->checkoutCart->getTotalsBlock()->waitForUpdatedTotals();
        $actualPrices['sub_total'] = $this->checkoutCart->getTotalsBlock()->getSubtotal();
        $actualPrices['grand_total'] = $this->checkoutCart->getTotalsBlock()->getGrandTotal();
        $actualPrices['discount'] = $this->checkoutCart->getTotalsBlock()->getDiscount();
        $expectedPrices = $this->cartPrice;

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedPrices,
            $actualPrices,
            'Wrong total cart prices are displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Shopping cart subtotal doesn't equal to grand total - price rule has been applied.";
    }
}
