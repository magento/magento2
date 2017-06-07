<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertChangePasswordFailMessage
 * Check that fail message is present
 */
class AssertChangePasswordFailMessage extends AbstractConstraint
{
    /**
     * Fail message
     */
    const FAIL_MESSAGE = 'The password doesn\'t match this account.';

    /**
     * Assert that fail message is present
     *
     * @param CustomerAccountEdit $customerAccountEdit
     * @return void
     */
    public function processAssert(CustomerAccountEdit $customerAccountEdit)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::FAIL_MESSAGE,
            $customerAccountEdit->getMessages()->getErrorMessage()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Fail message is displayed.';
    }
}
