<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

/**
 * Check that shopping cart free shipping is applied.
 */
class AssertCartPriceRuleFreeShippingIsApplied extends AssertCartPriceRuleApplying
{
    const FREE_SHIPPING_PRICE = '0.00';

    /**
     * Assert that free shipping is applied in shopping cart.
     *
     * @return void
     */
    protected function assert()
    {
        $shippingPrice = $this->checkoutCart->getTotalsBlock()->getShippingPrice();

        \PHPUnit_Framework_Assert::assertEquals(
            $shippingPrice,
            self::FREE_SHIPPING_PRICE,
            'Current shipping price: \'' . $shippingPrice
            . '\' not equals with free shipping price: \'' . self::FREE_SHIPPING_PRICE . '\''
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Free shipping is applied.';
    }
}
