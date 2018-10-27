<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that product is present in default wishlist.
 */
class AssertProductIsPresentInWishlist extends AbstractConstraint
{
    /**
     * Assert that product is present in default wishlist.
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
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->openLink('My Account');
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Wish List');

        \PHPUnit_Framework_Assert::assertTrue(
            $wishlistIndex->getWishlistBlock()->getProductItemsBlock()->getItemProduct($product)->isVisible(),
            $product->getName() . ' is not visible on Wish List page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is present in default Wish List.';
    }
}
