<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWishlistIsEmpty
 * Assert wish list is empty on 'My Account' page
 */
class AssertWishlistIsEmpty extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert wish list is empty
     *
     * @param CmsIndex $cmsIndex
     * @param WishlistIndex $wishlistIndex
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, WishlistIndex $wishlistIndex)
    {
        $cmsIndex->getLinksBlock()->openLink("My Wish List");
        \PHPUnit_Framework_Assert::assertTrue(
            $wishlistIndex->getWishlistBlock()->isEmptyBlockVisible(),
            'Wish list is not empty.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Wish list is empty.';
    }
}
