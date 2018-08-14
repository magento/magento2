<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class AssertAddProductToWishlistSuccessMessage
 * Assert that success message appears on Checkout Cart page after moving product to wishlist.
 */
class AssertMoveProductToWishlistSuccessMessage extends AbstractConstraint
{
    /**
     * Success add message
     */
    const SUCCESS_MESSAGE = "%s has been moved to your wish list.";

    /**
     * Assert that success message appears on My Wish List page after moving product to wishlist.
     *
     * @param WishlistIndex $wishlistIndex
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(WishlistIndex $wishlistIndex, InjectableFixture $product)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_MESSAGE, $product->getName()),
            $wishlistIndex->getMessagesBlock()->getSuccessMessage(),
            "Expected success move to wish list message doesn't match actual."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message appears on Checkout Cart page after moving product to Wish List.';
    }
}
