<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

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
        $backendOrderSidebarBlock =  $orderCreateIndex->getBackendOrderSidebarBlock();
        $backendOrderSidebarBlock->getSidebarWishlistBlock()->getWishlistItemsBlock()->noItemsInWishlistCheck();
        \PHPUnit_Framework_Assert::assertTrue(
            $backendOrderSidebarBlock->getSidebarWishlistBlock()->getWishlistItemsBlock()->noItemsInWishlistCheck(),
            "Assert that customer's Wish List section on Order Create backend page is not empty."
        );
    }

    /**
     * Success assert that customer's Wish List section on Order Create backend page is empty.
     *
     * @return string
     */
    public function toString()
    {
        return "Customer's Wish List section on Order Create backend page is empty.";
    }
}
