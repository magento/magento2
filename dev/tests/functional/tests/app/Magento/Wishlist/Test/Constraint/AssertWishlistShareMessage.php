<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWishlistShareMessage
 * Assert that after share wishlist successful message appears
 */
class AssertWishlistShareMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Success wishlist share message
     */
    const SUCCESS_MESSAGE = 'Your wish list has been shared.';

    /**
     * Assert that success message is displayed after wishlist has been shared
     *
     * @param WishlistIndex $wishlistIndex
     * @return void
     */
    public function processAssert(WishlistIndex $wishlistIndex)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $wishlistIndex->getMessagesBlock()->getSuccessMessage(),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Wish List success share message is present.';
    }
}
