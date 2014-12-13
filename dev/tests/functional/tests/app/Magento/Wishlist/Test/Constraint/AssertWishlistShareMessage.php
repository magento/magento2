<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Wishlist\Test\Constraint;

use Magento\Wishlist\Test\Page\WishlistIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWishlistShareMessage
 * Assert that after share wishlist successful message appears
 */
class AssertWishlistShareMessage extends AbstractConstraint
{
    /**
     * Success wishlist share message
     */
    const SUCCESS_MESSAGE = 'Your wish list has been shared.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that success message is displayed after wishlist has been shared
     *
     * @param WishlistIndex $wishlistIndex
     * @return void
     */
    public function processAssert(WishlistIndex $wishlistIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $wishlistIndex->getMessagesBlock()->getSuccessMessages(),
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
        return 'Wishlist success share message is present.';
    }
}
