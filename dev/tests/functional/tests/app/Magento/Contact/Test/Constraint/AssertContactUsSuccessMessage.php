<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Captcha\Test\Page\ContactUs;

/**
 * Assert that success message is present on "Contact Us" page.
 */
class AssertContactUsSuccessMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE =
        "Thanks for contacting us with your comments and questions. We'll respond to you very soon.";

    /**
     * Assert that success message is present on "Contact Us" page.
     *
     * @param ContactUs $contactUsPage
     * @return void
     */
    public function processAssert(ContactUs $contactUsPage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $contactUsPage->getMessagesBlock()->getMessage(),
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
