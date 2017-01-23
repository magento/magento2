<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Class AssertPagersPresentInShoppingCart
 * Assert that pagers are visible on checkout/cart/index page
 */
class AssertPagersPresentInShoppingCart extends AbstractConstraint
{
    /**
     * Verify that pagers aren't visible on the shopping cart page
     *
     * @param CheckoutCart $checkoutCart
     */
    public function processAssert(CheckoutCart $checkoutCart)
    {
        $checkoutCart->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $checkoutCart->getTopPagerBlock()->getPagesBlock()->isVisible(),
            'The top pager on the top of Items Grid is not visible'
            );
        \PHPUnit_Framework_Assert::assertTrue(
            $checkoutCart->getBottomPagerBlock()->getPagesBlock()->isVisible(),
            'The top pager on the bottom of Items Grid is not visible'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Pager isn\'t visible on the shopping cart page';
    }
}
