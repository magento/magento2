<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Assert that pagers aren't visible on checkout/cart/index page.
 */
class AssertPagersNotPresentInShoppingCart extends AbstractConstraint
{
    /**
     * Verify that pagers aren't visible on the shopping cart page.
     *
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart)
    {
        $checkoutCart->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $checkoutCart->getTopPagerBlock()->getPagesBlock()->isVisible(),
            'The top pager of Items Grid is visible.'
        );
        \PHPUnit_Framework_Assert::assertFalse(
            $checkoutCart->getBottomPagerBlock()->getPagesBlock()->isVisible(),
            'The bottom pager of Items Grid is visible.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Pager is absent on the shopping cart page.';
    }
}
