<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Assert that customer's Wish List section on Order Create backend page is empty.
 */
class AssertCustomerWishlistOnBackendIsEmpty extends AbstractConstraint
{
    /**
     * Assert that customer's Wish List section on Order Create backend page is empty.
     *
     * @param OrderCreateIndex $orderCreateIndex
     * @return void
     */
    public function processAssert(OrderCreateIndex $orderCreateIndex)
    {
        $orderCreateIndex->open();
        $orderCreateIndex->getSidebarWishlistBlock()->isSectionEmpty();
        \PHPUnit_Framework_Assert::assertTrue(
            $orderCreateIndex->getSidebarWishlistBlock()->isSectionEmpty(),
            "Assert that customer's Wish List section on Order Create backend page is not empty."
        );
    }

    /**
     * Assert success message that customer's Wish List section on Order Create backend page is empty.
     *
     * @return string
     */
    public function toString()
    {
        return "Customer's Wish List section on Order Create backend page is empty.";
    }
}
