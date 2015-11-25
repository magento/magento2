<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertAddedProductToCartSuccessMessage
 * Assert success message is appeared on Shopping Cart page
 */
class AssertAddedProductToCartSuccessMessage extends AbstractConstraint
{
    /**
     * Success add to cart message
     */
    const SUCCESS_MESSAGE = 'You added %s to your shopping cart.';

    /**
     * Assert success message is appeared on Shopping Cart page
     *
     * @param CheckoutCart $checkoutCart
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, FixtureInterface $product)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_MESSAGE, $product->getName()),
            $checkoutCart->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * Returns string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Add to cart success message is present on Shopping Cart page.';
    }
}
