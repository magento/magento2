<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountEdit;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertChangePasswordFailMessage
 * Check that fail message is present
 */
class AssertChangePasswordFailMessage extends AbstractConstraint
{
    /**
     * Fail message
     */
    const FAIL_MESSAGE = "Password doesn't match for this account.";

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
            $customerAccountEdit->getMessages()->getErrorMessages()
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
