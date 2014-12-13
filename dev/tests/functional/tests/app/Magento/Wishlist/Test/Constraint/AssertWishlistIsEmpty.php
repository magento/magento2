<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
