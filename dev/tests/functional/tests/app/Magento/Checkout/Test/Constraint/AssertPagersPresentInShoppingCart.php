<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Assert that pagers are visible on checkout/cart/index page.
 */
class AssertPagersPresentInShoppingCart extends AbstractConstraint
{
    /**
     * Verify that pagers are visible on the shopping cart page.
     *
     * @param CheckoutCart $checkoutCart
     */
    public function processAssert(CheckoutCart $checkoutCart)
    {
        $checkoutCart->open();
        \PHPUnit\Framework\Assert::assertTrue(
            $checkoutCart->getTopPagerBlock()->getPagesBlock()->isVisible(),
            'The top pager of Items Grid is not visible.'
        );
        \PHPUnit\Framework\Assert::assertTrue(
            $checkoutCart->getBottomPagerBlock()->getPagesBlock()->isVisible(),
            'The bottom pager of Items Grid is not visible.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Pager present on the shopping cart page.';
    }
}
