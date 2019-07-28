<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductRatingSuccessDeleteMessage
 */
class AssertProductRatingSuccessDeleteMessage extends AbstractConstraint
{
    const SUCCESS_DELETE_MESSAGE = 'You deleted the rating.';

    /**
     * Assert that success message is displayed after rating delete
     *
     * @param RatingIndex $ratingIndex
     * @return void
     */
    public function processAssert(RatingIndex $ratingIndex)
    {
        $actualMessage = $ratingIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text success delete message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Rating success delete message is present.';
    }
}
