<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class AssertAddProductToWishlistSuccessMessage
 * Assert that success message appears on My Wish List page after adding product to wishlist.
 */
class AssertAddProductToWishlistSuccessMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Success add message
     */
    const SUCCESS_MESSAGE = "%s has been added to your Wish List. Click here to continue shopping.";

    /**
     * Assert that success message appears on My Wish List page after adding product to wishlist.
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
            "Expected success message doesn't match actual."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message appears on My Wish List page after adding product to Wish List.';
    }
}
