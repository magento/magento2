<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\InjectableFixture;

/**
 * Class AssertProductIsPresentInWishlist
 * Assert that product is present in default wishlist
 */
class AssertProductIsPresentInWishlist extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that product is present in default wishlist
     *
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountIndex $customerAccountIndex
     * @param WishlistIndex $wishlistIndex
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CustomerAccountIndex $customerAccountIndex,
        WishlistIndex $wishlistIndex,
        InjectableFixture $product
    ) {
        $cmsIndex->getLinksBlock()->openLink('My Account');
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Wish List');

        \PHPUnit_Framework_Assert::assertTrue(
            $wishlistIndex->getItemsBlock()->getItemProduct($product)->isVisible(),
            $product->getName() . ' is not visible on wishlist page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is present in default wishlist.';
    }
}
