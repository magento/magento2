<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProceedToCheckoutButton
 * Assert it is possible to proceed to checkout from Shopping Cart page
 */
class AssertProceedToCheckoutButton extends AbstractConstraint
{
    /**
     * Success add to cart message
     */
    const BUTTON_TITLE = 'Proceed to Checkout';

    /**
     * Assert success message is appeared on Shopping Cart page
     *
     * @param CheckoutCart $checkoutCart
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart)
    {
        $checkoutCart->open();
        \PHPUnit\Framework\Assert::assertEquals(
            self::BUTTON_TITLE,
            $checkoutCart->getProceedToCheckoutBlock()->getTitle()
        );
    }

    /**
     * Returns string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Proceed To Checkout button presents in the Shopping Cart.';
    }
}
