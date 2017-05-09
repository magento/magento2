<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

class AssertProductPriceIsNotZero extends \Magento\Mtf\Constraint\AbstractConstraint
{
    /**
     * Assert that product price is not zero in default wishlist.
     *
     * @param \Magento\Cms\Test\Page\CmsIndex $cmsIndex
     * @param \Magento\Customer\Test\Page\CustomerAccountIndex $customerAccountIndex
     * @param \Magento\Wishlist\Test\Page\WishlistIndex $wishlistIndex
     * @param \Magento\Mtf\Fixture\InjectableFixture $product
     *
     * @return void
     */
    public function processAssert(
        \Magento\Cms\Test\Page\CmsIndex $cmsIndex,
        \Magento\Customer\Test\Page\CustomerAccountIndex $customerAccountIndex,
        \Magento\Wishlist\Test\Page\WishlistIndex $wishlistIndex,
        \Magento\Mtf\Fixture\InjectableFixture $product
    ) {
        $cmsIndex->getLinksBlock()->openLink('My Account');
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Wish List');
        $wishlistItem = $wishlistIndex->getWishlistBlock()->getProductItemsBlock()->getItemProduct($product);

        \PHPUnit_Framework_Assert::assertNotEquals(
            '0.00',
            $wishlistItem->getPrice(),
            $product->getName() . ' has zero price on Wish List page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product price is not zero in default Wish List.';
    }
}
