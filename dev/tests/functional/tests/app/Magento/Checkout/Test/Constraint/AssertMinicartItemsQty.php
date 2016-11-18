<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check items quantity in mini shopping cart.
 */
class AssertMinicartItemsQty extends AbstractConstraint
{
    /**
     * Assert items count in mini shopping cart.
     *
     * @param CmsIndex $cmsIndex,
     * @param int $expectedItemsQty
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        $expectedItemsQty
    ) {
        $cmsIndex->open();
        \PHPUnit_Framework_Assert::assertSame(
            (int)$expectedItemsQty,
            $cmsIndex->getCartSidebarBlock()->getItemsQty(),
            'The quantity of items in shopping cart is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'The quantity of items in mini shopping cart is correct.';
    }
}
