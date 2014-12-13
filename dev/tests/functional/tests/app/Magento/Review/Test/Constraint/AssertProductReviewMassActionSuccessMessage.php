<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Fixture\ReviewInjectable;
use Magento\Review\Test\Page\Adminhtml\ReviewIndex;
use Mtf\Constraint\AbstractConstraint;

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
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that success message is displayed after updated via mass actions
     *
     * @param ReviewInjectable|ReviewInjectable[] $review
     * @param ReviewIndex $reviewIndex
     * @return void
     */
    public function processAssert(ReviewInjectable $review, ReviewIndex $reviewIndex)
    {
        $reviews = is_array($review) ? $review : [$review];
        $successMessage = sprintf(self::SUCCESS_MESSAGE, count($reviews));
        \PHPUnit_Framework_Assert::assertEquals(
            $successMessage,
            $reviewIndex->getMessagesBlock()->getSuccessMessages(),
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
