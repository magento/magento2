<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customer cart is empty
 */
class AssertMinicartEmpty extends AbstractConstraint
{
    /**
     * Empty cart message
     */
    const TEXT_EMPTY_MINICART = 'You have no items in your shopping cart.';

    /**
     * Assert that customer minicart is empty
     *
     * @param CmsIndex $cmsIndex
     */
    public function processAssert(
        CmsIndex $cmsIndex
    ) {
        \PHPUnit_Framework_Assert::assertEquals(
            self::TEXT_EMPTY_MINICART,
            $cmsIndex->getCartSidebarBlock()->getEmptyMessage(),
            'Empty minicart message not found'
        );

        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getCartSidebarBlock()->isItemsQtyVisible(),
            'Minicart is not empty'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Minicart is empty';
    }
}
