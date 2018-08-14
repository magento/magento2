<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReviewMassActionSuccessDeleteMessage
 * Assert success message appears after deletion via mass actions
 */
class AssertProductReviewMassActionSuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Message that appears after deletion via mass actions
     */
    const SUCCESS_DELETE_MESSAGE = 'A total of %d record(s) have been deleted.';

    /**
     * Assert that success message is displayed after deletion via mass actions
     *
     * @param Review|Review[] $review
     * @param ReviewIndex $reviewIndex
     * @return void
     */
    public function processAssert(Review $review, ReviewIndex $reviewIndex)
    {
        $reviews = is_array($review) ? $review : [$review];
        $deleteMessage = sprintf(self::SUCCESS_DELETE_MESSAGE, count($reviews));
        \PHPUnit_Framework_Assert::assertEquals(
            $deleteMessage,
            $reviewIndex->getMessagesBlock()->getSuccessMessage(),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Text success save message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Review success message appears after deletion via mass actions is present.';
    }
}
