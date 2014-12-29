<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Review\Test\Constraint;

use Magento\Review\Test\Page\Adminhtml\RatingIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductRatingSuccessSaveMessage
 */
class AssertProductRatingSuccessSaveMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    const SUCCESS_MESSAGE = 'You saved the rating.';

    /**
     * Assert that success message is displayed after rating save
     *
     * @param RatingIndex $ratingIndex
     * @return void
     */
    public function processAssert(RatingIndex $ratingIndex)
    {
        $actualMessage = $ratingIndex->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text success save message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Rating success save message is present.';
    }
}
