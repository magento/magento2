<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Constraint;

use Magento\Contact\Test\Page\ContactIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that success message is present on "Contact Us" page.
 */
class AssertContactUsSuccessMessage extends AbstractConstraint
{
    /**
     * Success sent comment message(the first part).
     */
    const SUCCESS_MESSAGE_PART_1 = "Thanks for contacting us with your comments and questions. ";

    /**
     * Success sent comment message(the second part).
     */
    const SUCCESS_MESSAGE_PART_2 = "We'll respond to you very soon.";

    /**
     * Assert that success message is present on "Contact Us" page.
     *
     * @param ContactIndex $contactIndex
     * @return void
     */
    public function processAssert(ContactIndex $contactIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE_PART_1 . self::SUCCESS_MESSAGE_PART_2,
            $contactIndex->getMessagesBlock()->getMessage(),
            'Wrong message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Success message is present on contact us page.';
    }
}
