<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductReviewBackendSuccessSaveMessage
 * Assert that success message is displayed after review created
 */
class AssertProductReviewBackendSuccessSaveMessage extends AbstractConstraint
{
    /**
     * Text of success message after review created
     */
    const SUCCESS_MESSAGE = 'You saved the review.';

    /**
     * Assert that success message is displayed after review created
     *
     * @param ReviewIndex $reviewIndex
     * @return void
     */
    public function processAssert(ReviewIndex $reviewIndex)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $reviewIndex->getMessagesBlock()->getSuccessMessage(),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Text success create message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Review success create message is present.';
    }
}
