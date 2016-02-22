<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Fixture\Review;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReviewMassActionSuccessMessage
 * Assert success message appears after updated via mass actions
 */
class AssertProductReviewMassActionSuccessMessage extends AbstractConstraint
{
    /**
     * Message that appears after updates via mass actions
     */
    const SUCCESS_MESSAGE = 'A total of %d record(s) have been updated.';

    /**
     * Assert that success message is displayed after updated via mass actions
     *
     * @param Review|Review[] $review
     * @param ReviewIndex $reviewIndex
     * @return void
     */
    public function processAssert(Review $review, ReviewIndex $reviewIndex)
    {
        $reviews = is_array($review) ? $review : [$review];
        $successMessage = sprintf(self::SUCCESS_MESSAGE, count($reviews));
        \PHPUnit_Framework_Assert::assertEquals(
            $successMessage,
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
        return 'Review success message appears after updated via mass actions is present.';
    }
}
